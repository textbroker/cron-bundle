<?php
declare(strict_types=1);

namespace MH1\CronBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use MH1\CronBundle\Entity\Mh1CronJob;
use MH1\CronBundle\Entity\Mh1CronJobReport;
use MH1\CronBundle\Helper\DateTimeHelper;
use MH1\CronBundle\Model\CronJobInterface;
use MH1\CronBundle\Model\CronJobReportInterface;

class DoctrineCronJobLogService implements CronJobLogServiceInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @inheritDoc
     */
    public function logStart(CronJobInterface $cronJob): CronJobReportInterface
    {
        /** @var Mh1CronJob $cronJob */
        $runReport = new Mh1CronJobReport($cronJob, DateTimeHelper::getUTCDateTime());

        $this->entityManager->persist($runReport);
        $this->entityManager->flush();

        return $runReport;
    }

    /**
     * @inheritDoc
     */
    public function logEnd(
        CronJobReportInterface $runReport,
        ?int                   $exitCode,
        string                 $output
    ): CronJobReportInterface {
        $endTime = DateTimeHelper::getUTCDateTime();
        $runReport->setEndTime($endTime);
        $runReport->setExitCode($exitCode);
        $runReport->setOutput($output);
        $runReport->setDuration($endTime->getTimestamp() - $runReport->getStartTime()->getTimestamp());

        $this->entityManager->persist($runReport);
        $this->entityManager->flush();

        return $runReport;
    }
}
