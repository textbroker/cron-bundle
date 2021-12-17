<?php
declare(strict_types=1);

namespace MH1\CronBundle\Tests\Unit\Service;

use MH1\CronBundle\Service\CronCommandHelperService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

class CronCommandHelperServiceTest extends TestCase
{
    private const LOCK_PREFIX = 'sample_lockPrefix_';

    /**
     * @var CronCommandHelperService
     */
    private $helperService;

    /**
     * @var MockObject|LockFactory
     */
    private $lockMock;

    protected function setUp(): void
    {
        $this->lockMock = $this->createMock(LockFactory::class);
        $this->helperService = new CronCommandHelperService(
            $this->lockMock,
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

        $this->helperService->trackCommandIsLocked($output, $cronName);
    }

    public function testGetLockName(): void
    {
        $cronName = 'app:cron:sample';
        $lockName = $this->helperService->getLockName($cronName);

        self::assertSame(self::LOCK_PREFIX . $cronName, $lockName);
    }

    public function testLockCommand(): void
    {
        /** @var LockInterface|MockObject $lock */
        $lock = $this->createMock(LockInterface::class);
        $lock->expects(self::once())
             ->method('acquire')
             ->willReturn(true)
        ;

        $this->lockMock
            ->expects(self::once())
            ->method('createLock')
            ->with($this->helperService->getLockName('app:test'))
            ->willReturn($lock)
        ;

        self::assertTrue($this->helperService->lockCommand('app:test'));
    }

    public function testReleaseCommand(): void
    {
        /** @var LockInterface|MockObject $lock */
        $lock = $this->createMock(LockInterface::class);
        $lock->expects(self::once())
             ->method('acquire')
             ->willReturn(true)
        ;
        $lock->expects(self::once())
             ->method('release')
             ->willReturn(true)
        ;

        $this->lockMock
            ->expects(self::once())
            ->method('createLock')
            ->with($this->helperService->getLockName('app:test'))
            ->willReturn($lock)
        ;

        $this->helperService->releaseCommand();

        $this->helperService->lockCommand('app:test');

        $this->helperService->releaseCommand();
    }
}
