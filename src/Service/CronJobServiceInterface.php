<?php
declare(strict_types=1);

namespace MH1\CronBundle\Service;

use MH1\CronBundle\Model\CronJobInterface;

interface CronJobServiceInterface
{
    /**
     * @param iterable<CronJobInterface>|null $cronJobs
     */
    public function execute(?iterable $cronJobs = null): void;
}
