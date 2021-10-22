[![CI](https://github.com/m-h-1/cron-bundle/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/m-h-1/cron-bundle/actions/workflows/ci.yml)
![coverage](./docs/coverage_badge.svg)

# cron-bundle

Simple Symfony bundle to schedule commands.
Uses [symfony/lock](https://symfony.com/doc/current/components/lock.html) to make sure a specific command can't run more than once at the same time. 
Different commands are executed in parallel.

## TOC

* [Usage](#usage)
  * [Register new job](#register-new-job)
  * [Execute jobs](#execute-jobs)
  * [Stalled job executions](#stalled-job-executions)
* [Installation](#installation)
  * [Step 1: Download the bundle](#step-1-download-the-bundle)
  * [Step 2: Enable the bundle](#step-2-enable-the-bundle)
  * [Step 3: Create DB tables](#step-3-create-db-tables)
  * [Step 4: Add to crontab](#step-4-add-to-crontab)
* [Config](#config)


## Usage

### Register new job

Simply register a new job by creating a new entry in `mh1_cron_job`.

```sql
INSERT INTO mh1_cron_job (id, title, description, command, enabled, schedule, execute_stalled)
VALUES ('d63a6a70-0b56-4b0d-bdfb-06e4c7e7a2eb', 'Symfony Help Command', 'display the Symfony help', 'help', 1, '*/15 * * * *', 1);
```
|FieldName|Description|
|---------|-----------|
|`id`|uuidv4|
|`title`|Title of the command|
|`description`|Optional description|
|`command`|Executable Symfony command name|
|`enabled`|TRUE/1 if job should be enabled for execution, otherwise FALSE/0|
|`schedule`|Crontab style schedule [help](https://crontab.guru/)|
|`execute_stalled`|[see](#stalled-job-executions)|

### Execute jobs

```console
$ bin/console mh1:cron:run
```

### Stalled job executions

**This only works for jobs that ran at least once in the past!**

The Doctrine integration also provides the possibility to execute stalled jobs. 

A stalled job is a job that was supposed to be executed at a specific time but was not executed due to being temporarily disabled or 
`bin/console mh1:cron:run` was not run, but should be run once the job is enabled again.

For example, you have a job that runs every night at 1 AM, but for some reason (e.g. server maintenance) 
the jobs are not executed between 0:30 AM and 3 AM.
If `executeStalled` (`execute_stalled` DB column) is set to true, the job will be executed after the maintenance window at 3 AM.

## Installation

### Step 1: Download the Bundle

```console
$ composer require m-h-1/cron-bundle
```

### Step 2: Enable the Bundle

```php
// config/bundles.php

return [
    // ...
    MH1\CronBundle\MH1CronBundle::class => ['all' => true],
];
```

### Step 3: Create DB Tables

Create tables for the Doctrine entities:

#### DoctrineMigrations

```console
$ bin/console doctrine:migrations:diff
$ bin/console doctrine:migrations:migrate
```

#### raw SQL

```sql
CREATE TABLE mh1_cron_job
(
    id              CHAR(36)     NOT NULL PRIMARY KEY,
    title           VARCHAR(255) NOT NULL,
    description     VARCHAR(255) NULL,
    command         VARCHAR(255) NOT NULL,
    enabled         TINYINT(1)   NOT NULL,
    schedule        VARCHAR(255) NOT NULL,
    execute_stalled TINYINT(1)   NOT NULL
) COLLATE = utf8mb4_unicode_ci;

CREATE TABLE mh1_cron_job_report
(
    id          INT AUTO_INCREMENT PRIMARY KEY,
    cron_job_id CHAR(36) NOT NULL,
    start_time  DATETIME NOT NULL,
    end_time    DATETIME NULL,
    exit_code   INT      NULL,
    output      LONGTEXT NULL,
    duration    INT      NULL,
    CONSTRAINT fk_a297993479099ed8 FOREIGN KEY (cron_job_id) REFERENCES mh1_cron_job (id)
) COLLATE = utf8mb4_unicode_ci;

CREATE INDEX idx_a297993479099ed8 ON mh1_cron_job_report (cron_job_id);
```

### Step 4: Add to crontab

Add the command to crontab and replace PATH_TO_APPLICATION with the path to your Symfony project directory.

```console
* * * * *   PATH_TO_APPLICATION/bin/console mh1:cron:run
```

## Config

```yaml
# config/packages/mh1_cron.yaml
mh1_cron:
    service: null # override job service with a custom service
    log_service: null # override logging service
    check_interval: 1000 # milliseconds to wait between the checks if a process is running (must be greater than 1)
    execution_time_zone: null # use a custom time zone for job scheduling, the default is the PHP default timezone
    lock_prefix: '' # use a prefix for cronjob logging, the default is empty string
```

#### Custom job service

```yaml
# config/packages/mh1_cron.yaml
mh1_cron:
    service: App\Service\CustomCronJobService
```

#### Different logging service

```yaml
# config/packages/mh1_cron.yaml
mh1_cron:
    log_service: App\Service\CustomLogService
```


#### Wait half a second (instead of one second) between the checkRunning calls

```yaml
# config/packages/mh1_cron.yaml
mh1_cron:
    check_interval: 500
```

#### Use a custom time zone to check for due time

Every PHP supported timezone string is valid https://www.php.net/manual/en/timezones.php

```yaml
# config/packages/mh1_cron.yaml
mh1_cron:
    execution_time_zone: 'UTC'
```
```yaml
# config/packages/mh1_cron.yaml
mh1_cron:
    execution_time_zone: 'Europe/Berlin'
```

#### Prefix lock name

The symfony lock component uses the commands name (`app:run`) as the name for the lock.

If you want to run the same command in different deployments or folders on the same system you have to use 
this parameter to prefix the name of the locks, e.g. `instance1:app:run`, `instance2:app:run`.

```yaml
# config/packages/mh1_cron.yaml
mh1_cron:
    lock_prefix: 'second_instance'
```
