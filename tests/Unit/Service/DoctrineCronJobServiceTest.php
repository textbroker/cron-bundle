<?php
declare(strict_types=1);

namespace MH1\CronBundle\Tests\Unit\Service;

use DateInterval;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use MH1\CronBundle\Entity\Mh1CronJob;
use MH1\CronBundle\Entity\Mh1CronJobReport;
use MH1\CronBundle\Helper\DateTimeHelper;
use MH1\CronBundle\Repository\Mh1CronJobReportRepository;
use MH1\CronBundle\Repository\Mh1CronJobRepository;
use MH1\CronBundle\Service\CronJobLogServiceInterface;
use MH1\CronBundle\Service\DoctrineCronJobService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class DoctrineCronJobServiceTest extends TestCase
{
    /**
     * @var EntityManagerInterface|MockObject
     */
    private $entityManagerMock;

    /**
     * @var DoctrineCronJobService
     */
    private $service;

    private $serverTimezone = 'America/New_York';
    private $executionTimeZone = 'America/Los_Angeles';

    public function setUp(): void
    {
        date_default_timezone_set($this->serverTimezone);
        $cronJobLogServiceMock = $this->createMock(CronJobLogServiceInterface::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->service = new DoctrineCronJobService(
            $cronJobLogServiceMock,
            $this->entityManagerMock,
            '',
            1000,
            $this->executionTimeZone
        );
    }

    /**
     * @return array
     */
    public function provideGetActiveJobs(): array
    {
        return [
            'no jobs'  => [[]],
            'two jobs' => [
                [
                    $this->getJob('* * * * *'),
                    $this->getJob('* * * * *'),
                ]
            ]
        ];
    }

    /**
     * @param string $schedule
     * @param bool   $stalled
     *
     * @return Mh1CronJob
     */
    private function getJob(string $schedule, bool $stalled = false): Mh1CronJob
    {
        return (new Mh1CronJob())
            ->setId(Uuid::uuid4())
            ->setEnabled(true)
            ->setSchedule($schedule)
            ->setTitle('')
            ->setDescription('')
            ->setCommand('app:test:test')
            ->setExecuteStalled($stalled)
        ;
    }

    /**
     * @dataProvider provideGetActiveJobs
     *
     * @param array $jobs
     */
    public function testGetActiveJobs(array $jobs): void
    {
        $this->setUpActiveJobMocks($jobs, 1);
        $activeJobs = $this->service->getEnabledJobs();
        self::assertSame($jobs, $activeJobs);
    }

    /**
     * @param array $jobs
     * @param int   $repoCalls
     */
    private function setUpActiveJobMocks(array $jobs, int $repoCalls): void
    {
        $lastExecution = null;
        if (count($jobs) !== 0) {
            $lastExecution = new Mh1CronJobReport(current($jobs), new DateTime());
            $lastExecution->setEndTime((new DateTime())->sub(new DateInterval('P5D')));
        }

        $repoMock = $this->createMock(Mh1CronJobRepository::class);
        $repoMock->expects(self::once())
                 ->method('findEnabledJobs')
                 ->willReturn($jobs)
        ;

        $reportRepoMock = $this->createMock(Mh1CronJobReportRepository::class);
        $reportRepoMock->method('findLastExecutionByJob')
                       ->willReturn($lastExecution)
        ;

        $this->entityManagerMock->expects(self::exactly($repoCalls))
                                ->method('getRepository')
                                ->willReturnCallback(
                                    static function (string $className) use ($repoMock, $reportRepoMock) {
                                        if ($className === Mh1CronJob::class) {
                                            return $repoMock;
                                        }
                                        if ($className === Mh1CronJobReport::class) {
                                            return $reportRepoMock;
                                        }
                                        return null;
                                    }
                                )
        ;
    }

    /**
     * @return array
     */
    public function provideGetScheduledJobs(): array
    {
        $dateTime = DateTimeHelper::getUTCDateTimeImmutable();
        $dateTime = $dateTime->setTimezone(new DateTimeZone($this->executionTimeZone));
        $dateTimeAdd2 = $dateTime->add(new DateInterval('PT2H'));
        $dateTimeAdd4 = $dateTime->add(new DateInterval('PT4H'));
        $dateTimeAdd12 = $dateTime->add(new DateInterval('PT12H'));
        $jobEveryMinute = $this->getJob('* * * * *');
        $jobCurrentHour = $this->getJob('* ' . ($dateTime->format('G')) . ' * * *');
        $jobOtherHourStalled = $this->getJob('* ' . $dateTimeAdd2->format('G') . ' * * *', true);
        $jobOtherHour2 = $this->getJob('* ' . $dateTimeAdd2->format('G') . ' * * *');
        $jobOtherHour12 = $this->getJob('* ' . $dateTimeAdd12->format('G') . ' * * *');
        $jobs = [
            $jobEveryMinute,
            $jobCurrentHour,
            $jobOtherHourStalled,
            $jobOtherHour2,
            $jobOtherHour12,
        ];
        return [
            'no jobs'           => [[], [], null, 1],
            'no scheduled jobs' => [
                [
                    $jobOtherHour2,
                    $jobOtherHour12,
                ],
                [],
                null,
                1
            ],
            'no DateTime'       => [
                $jobs,
                [
                    $jobEveryMinute,
                    $jobCurrentHour,
                    $jobOtherHourStalled,
                ],
            ],
            'current DateTime'  => [
                $jobs,
                [
                    $jobEveryMinute,
                    $jobCurrentHour,
                    $jobOtherHourStalled,
                ],
                $dateTime
            ],
            'DateTime + 2'      => [
                $jobs,
                [
                    $jobEveryMinute,
                    $jobOtherHourStalled,
                    $jobOtherHour2,
                ],
                $dateTimeAdd2,
                1
            ],
            'DateTime + 4'      => [
                $jobs,
                [
                    $jobEveryMinute,
                    $jobOtherHourStalled,
                ],
                $dateTimeAdd4
            ],
            'DateTime + 12'     => [
                $jobs,
                [
                    $jobEveryMinute,
                    $jobOtherHourStalled,
                    $jobOtherHour12,
                ],
                $dateTimeAdd12
            ]
        ];
    }

    /**
     * @dataProvider provideGetScheduledJobs
     *
     * @param array         $jobs
     * @param array         $expectedScheduledJobs
     * @param DateTime|null $scheduleDateTime
     * @param int           $expectedGetRepoCalls
     */
    public function testGetScheduledJobs(
        array              $jobs,
        array              $expectedScheduledJobs,
        ?DateTimeInterface $scheduleDateTime = null,
        int                $expectedGetRepoCalls = 2
    ): void {
        $this->setUpActiveJobMocks($jobs, $expectedGetRepoCalls);
        $scheduledJobs = $this->service->getScheduledJobs($scheduleDateTime);

        self::assertSame($expectedScheduledJobs, $scheduledJobs);
    }

    /**
     * @return array[]
     */
    public function provideCreateProcess(): array
    {
        $job = new Mh1CronJob();
        $job->setCommand('app:test:runner');

        return [
            'without path'     => ['', null, $job, '"" \'app:test:runner\''],
            'without php path' => [
                '/var/www/app/bin/console',
                null,
                $job,
                '\'/var/www/app/bin/console\' \'app:test:runner\''
            ],
            'without empty php path' => [
                '/var/www/app/bin/console',
                '',
                $job,
                '\'/var/www/app/bin/console\' \'app:test:runner\''
            ],
            'php path'         => [
                '/var/www/app/bin/console',
                '/usr/local/php81/bin/php',
                $job,
                '\'/usr/local/php81/bin/php\' \'/var/www/app/bin/console\' \'app:test:runner\''
            ],
        ];
    }

    /**
     * @dataProvider provideCreateProcess
     *
     * @param string      $consolePath
     * @param string|null $phpPath
     * @param Mh1CronJob  $cronJob
     * @param string      $expectedPath
     */
    public function testCreateProcess(
        string     $consolePath,
        ?string    $phpPath,
        Mh1CronJob $cronJob,
        string     $expectedPath
    ): void {
        $cronJobLogServiceMock = $this->createMock(CronJobLogServiceInterface::class);
        $service = new DoctrineCronJobService(
            $cronJobLogServiceMock,
            $this->entityManagerMock,
            $consolePath,
            1000,
            $this->executionTimeZone,
            $phpPath
        );

        $process = $service->createProcess($cronJob);

        self::assertSame($expectedPath, $process->getCommandLine());
    }
}
