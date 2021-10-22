<?php
declare(strict_types=1);

namespace MH1\CronBundle\Tests\Unit\Service;

use MH1\CronBundle\Service\CronCommandHelperService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronCommandHelperServiceTest extends TestCase
{
    private const LOCK_PREFIX = 'sample_lockPrefix_';

    /**
     * @var CronCommandHelperService
     */
    private $helperService;

    /**
     * @var MockObject|LoggerInterface
     */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->helperService = new CronCommandHelperService(
            $this->loggerMock,
            self::LOCK_PREFIX
        );
    }

    public function testCommandIsLocked(): void
    {
        $cronName = 'app:cron:sample';

        /** @var OutputInterface|MockObject $output */
        $output = $this->createMock(OutputInterface::class);
        $output->expects(self::once())
               ->method('writeln')
               ->with('The command is already running in another process.')
        ;

        $this->loggerMock
            ->expects(self::once())
            ->method('error')
            ->with(self::LOCK_PREFIX . $cronName . ': process running longer than expected')
        ;

        $this->helperService->commandIsLocked($output, $cronName);
    }

    public function testGetLockName(): void
    {
        $cronName = 'app:cron:sample';
        $lockName = $this->helperService->getLockName($cronName);

        self::assertSame(self::LOCK_PREFIX . $cronName, $lockName);
    }
}
