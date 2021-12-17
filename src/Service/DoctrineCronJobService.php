<?php
declare(strict_types=1);

namespace MH1\CronBundle\Service;

use Cron\CronExpression;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use MH1\CronBundle\Entity\Mh1CronJob;
use MH1\CronBundle\Entity\Mh1CronJobReport;
use MH1\CronBundle\Helper\DateTimeHelper;
use MH1\CronBundle\Model\CronJobInterface;
use MH1\CronBundle\Repository\Mh1CronJobReportRepository;
use MH1\CronBundle\Repository\Mh1CronJobRepository;

class DoctrineCronJobService extends AbstractCronJobService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @inheritDoc
     */
    public function __construct(
        CronJobLogServiceInterface $cronJobLogService,
        EntityManagerInterface     $entityManager,
        string                     $consolePath,
        int                        $checkInterval,
        ?string                    $executionTimeZone = null,
        ?string                    $phpExecutablePath = null
    ) {
        $this->entityManager = $entityManager;
        parent::__construct($cronJobLogService, $consolePath, $checkInterval, $executionTimeZone, $phpExecutablePath);
    }

    /**
     * @inheritDoc
     */
    public function getEnabledJobs(): array
    {
        /** @var Mh1CronJobRepository $repository */
        $repository = $this->entityManager->getRepository(Mh1CronJob::class);
        return $repository->findEnabledJobs();
    }

    /**
     * @param CronJobInterface       $job
     * @param DateTimeInterface|null $dateTime
     *
     * @return bool
     * @throws Exception
     */
    protected function shouldJobRun(CronJobInterface $job, ?DateTimeInterface $dateTime = null): bool
    {
        $dateTime = $dateTime ?? DateTimeHelper::getUTCDateTime();

        if (parent::shouldJobRun($job, $dateTime)) {
            return true;
        }

        // job should be an entity and configured as execute on stalled
        if (!($job instanceof Mh1CronJob && $job->isExecuteStalled())) {
            return false;
        }

        /** @var Mh1CronJobReportRepository $reportRepository */
        $reportRepository = $this->entityManager->getRepository(Mh1CronJobReport::class);

        // get the last execution of the cronjob
        $lastExecution = $reportRepository->findLastExecutionByJob($job);

        // if no last execution or no last finished execution found
        if ($lastExecution === null || $lastExecution->getEndTime() === null) {
            return false;
        }

        $cron = new CronExpression($job->getSchedule());
        $nextRunDate = $cron->getNextRunDate($lastExecution->getEndTime(), 0, false, $this->executionTimeZone);

        // check if the next runtime is before the current checking time
        return $nextRunDate <= $dateTime;
    }
}
