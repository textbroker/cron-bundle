<?php
declare(strict_types=1);

namespace MH1\CronBundle\Tests\Unit\Service;

use Doctrine\ORM\EntityManagerInterface;
use MH1\CronBundle\Entity\Mh1CronJob;
use MH1\CronBundle\Entity\Mh1CronJobReport;
use MH1\CronBundle\Helper\DateTimeHelper;
use MH1\CronBundle\Service\DoctrineCronJobLogService;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @group time-sensitive
 */
class DoctrineCronJobLogServiceTest extends TestCase
{
    /**
     * @var DoctrineCronJobLogService
     */
    private $doctrineCronJobLogService;

    public function testLogging(): void
    {
        $exitCode = 1;
        $output = "checked\nrun success";
        $cronJob = new Mh1CronJob();
        $uuid = Uuid::uuid4();
        $cronJob->setId($uuid);
        $cronJob->setTitle('test123');
        $cronJob->setCommand('test:command');
        $startTime = DateTimeHelper::getUTCDateTime()->getTimestamp();

        /** @var Mh1CronJobReport $startReport */
        $startReport = $this->doctrineCronJobLogService->logStart($cronJob);

        self::assertSame(0, $uuid->compareTo($startReport->getCronJob()->getId()));
        self::assertSame(99, $startReport->getId());
        self::assertSame($startTime, $startReport->getStartTime()->getTimestamp());
        self::assertNull($startReport->getExitCode());
        self::assertNull($startReport->getOutput());
        self::assertNull($startReport->getDuration());

        sleep(5);

        $cronReport = $this->doctrineCronJobLogService->logEnd($startReport, $exitCode, $output);
        self::assertSame($cronJob->getTitle(), $cronReport->getCronJob()->getTitle());
        self::assertSame($cronJob->getCommand(), $cronReport->getCronJob()->getCommand());
        self::assertSame($startTime, $cronReport->getStartTime()->getTimestamp());
        self::assertSame($startTime + 5, $cronReport->getEndTime()->getTimestamp());
        self::assertSame(5, $cronReport->getDuration());
    }

    protected function setUp(): void
    {
        $entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $entityManagerMock->expects(self::exactly(2))
                          ->method('persist')
                          ->willReturnCallback(static function ($entity) {
                              $entity->setId(99);
                          })
        ;
        $entityManagerMock->expects(self::exactly(2))->method('flush')
        ;
        $this->doctrineCronJobLogService = new DoctrineCronJobLogService($entityManagerMock);
    }
}
