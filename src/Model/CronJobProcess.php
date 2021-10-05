<?php
declare(strict_types=1);

namespace MH1\CronBundle\Model;

use Symfony\Component\Process\Process;

class CronJobProcess
{
    /**
     * @var Process<callable>
     */
    private $process;

    /**
     * @var CronJobReportInterface
     */
    private $report;

    /**
     * @param Process<callable>      $process
     * @param CronJobReportInterface $log
     */
    public function __construct(Process $process, CronJobReportInterface $log)
    {
        $this->process = $process;
        $this->report = $log;
    }

    /**
     * @return Process<callable>
     */
    public function getProcess(): Process
    {
        return $this->process;
    }

    /**
     * @return CronJobReportInterface
     */
    public function getReport(): CronJobReportInterface
    {
        return $this->report;
    }
}
