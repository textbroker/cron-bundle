<?php
declare(strict_types=1);

namespace MH1\CronBundle\Service;

use Cron\CronExpression;
use DateTimeInterface;
use InvalidArgumentException;
use MH1\CronBundle\Helper\DateTimeHelper;
use MH1\CronBundle\Model\CronJobInterface;
use MH1\CronBundle\Model\CronJobProcess;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;

abstract class AbstractCronJobService implements CronJobServiceInterface
{
    /**
     * @var CronJobLogServiceInterface
     */
    protected $cronJobLogService;

    /**
     * @var string
     */
    protected $consolePath;

    /**
     * @var int
     */
    private $checkInterval;

    /**
     * @var string
     */
    protected $executionTimeZone;

    /**
     * @var string|null
     */
    private $phpExecutablePath;

    /**
     * @param CronJobLogServiceInterface $cronJobLogService
     * @param string                     $consolePath       path to bin/console e.g. "/var/www/project/bin/console"
     * @param int                        $checkInterval     microseconds to wait between the check if a
     *                                                      process is running (must be greater than 10)
     * @param string|null                $executionTimeZone name of the timezone to check for the runtime
     *                                                      if null php default timezone is used
     * @param string|null                $phpExecutablePath path to php executable e.g. /usr/local/php81/bin/php
     *                                                      usefully if multiple PHP versions running on the same system
     */
    public function __construct(
        CronJobLogServiceInterface $cronJobLogService,
        string                     $consolePath,
        int                        $checkInterval,
        ?string                    $executionTimeZone = null,
        ?string                    $phpExecutablePath = null
    ) {
        if ($checkInterval <= 1) {
            throw new InvalidArgumentException(
                'checkInterval must be greater than 1 millisecond to prevent errors'
            );
        }

        $this->cronJobLogService = $cronJobLogService;
        $this->consolePath = $consolePath;
        $this->checkInterval = $checkInterval * 1000;
        $this->executionTimeZone = $executionTimeZone ?? date_default_timezone_get();
        $this->phpExecutablePath = $phpExecutablePath;
    }

    /**
     * @inheritDoc
     */
    public function execute(?iterable $cronJobs = null): void
    {
        $cronJobs = $cronJobs ?? $this->getScheduledJobs();

        $processes = [];
        foreach ($cronJobs as $cronJob) {
            $log = $this->cronJobLogService->logStart($cronJob);

            /** @var Process<callable> $process */
            $process = $this->createProcess($cronJob);
            try {
                $process->start();
            } catch (RuntimeException|LogicException $exception) {
                $this->cronJobLogService->logEnd($log, Command::FAILURE, $exception->getMessage());
                continue;
            }
            $processes[] = new CronJobProcess($process, $log);
        }

        $this->checkRunning($processes);
    }

    /**
     * @param CronJobInterface $cronJob
     *
     * @return Process
     */
    public function createProcess(CronJobInterface $cronJob): Process
    {
        $processParts = [];
        if ($this->phpExecutablePath !== null && $this->phpExecutablePath !== '') {
            $processParts[] = $this->phpExecutablePath;
        }

        $processParts[] = $this->consolePath;
        $processParts[] = $cronJob->getCommand();

        return new Process($processParts);
    }

    /**
     * @param DateTimeInterface|null $scheduleDateTime
     *
     * @return CronJobInterface[]
     */
    public function getScheduledJobs(DateTimeInterface $scheduleDateTime = null): array
    {
        $scheduleDateTime = $scheduleDateTime ?? DateTimeHelper::getUTCDateTimeImmutable();
        $enabledJobs = $this->getEnabledJobs();

        $scheduledJobs = array_filter($enabledJobs, function ($job) use ($scheduleDateTime) {
            return $this->shouldJobRun($job, $scheduleDateTime);
        });

        return array_values($scheduledJobs);
    }

    /**
     * @return CronJobInterface[]
     */
    abstract public function getEnabledJobs(): array;

    /**
     * @param CronJobInterface       $job
     * @param DateTimeInterface|null $dateTime
     *
     * @return bool
     */
    protected function shouldJobRun(CronJobInterface $job, ?DateTimeInterface $dateTime = null): bool
    {
        $dateTime = $dateTime ?? DateTimeHelper::getUTCDateTime();
        $cron = new CronExpression($job->getSchedule());
        return $cron->isDue($dateTime, $this->executionTimeZone);
    }

    /**
     * @param CronJobProcess[] $processes
     */
    protected function checkRunning(array $processes): void
    {
        $runningProcesses = [];

        foreach ($processes as $process) {
            if ($process->getProcess()->isRunning()) {
                $runningProcesses[] = $process;
                continue;
            }

            $this->cronJobLogService->logEnd(
                $process->getReport(),
                $process->getProcess()->getExitCode(),
                $process->getProcess()->getOutput() . PHP_EOL . PHP_EOL . $process->getProcess()->getErrorOutput()
            );
        }

        if (count($runningProcesses) > 0) {
            usleep($this->checkInterval);
            $this->checkRunning($runningProcesses);
        }
    }
}
