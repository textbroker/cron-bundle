<?php
declare(strict_types=1);

namespace MH1\CronBundle\Service;

use MH1\CronBundle\Model\CronJobInterface;
use MH1\CronBundle\Model\CronJobReportInterface;

/**
 * Interface CronJobLogServiceInterface
 */
interface CronJobLogServiceInterface
{
    /**
     * @param CronJobInterface $cronJob
     *
     * @return CronJobReportInterface
     */
    public function logStart(CronJobInterface $cronJob): CronJobReportInterface;

    /**
     * @param CronJobReportInterface $runReport
     * @param int|null               $exitCode
     * @param string                 $output
     *
     * @return CronJobReportInterface
     */
    public function logEnd(
        CronJobReportInterface $runReport,
        ?int                    $exitCode,
        string                 $output
    ): CronJobReportInterface;
}
