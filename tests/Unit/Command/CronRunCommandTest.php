<?php
declare(strict_types=1);

namespace MH1\CronBundle\Tests\Unit\Command;

use MH1\CronBundle\Command\CronRunCommand;
use MH1\CronBundle\Service\CronJobServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class CronRunCommandTest extends TestCase
{
    public function testExecute(): void
    {
        $cronJobService = $this->createMock(CronJobServiceInterface::class);
        $cronJobService->expects(self::once())->method('execute')
        ;
        $command = new CronRunCommand($cronJobService);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());
    }
}
