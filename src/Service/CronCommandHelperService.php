<?php
declare(strict_types=1);

namespace MH1\CronBundle\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

class CronCommandHelperService implements CronCommandHelperServiceInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $lockPrefix;

    public function __construct(LoggerInterface $logger, string $lockPrefix = '')
    {
        $this->logger = $logger;
        $this->lockPrefix = $lockPrefix;
    }

    /**
     * @inheritDoc
     */
    public function commandIsLocked(OutputInterface $output, string $name): int
    {
        $output->writeln('The command is already running in another process.');

        // log an error if the process is running longer than expected through the schedule
        $this->logger->error($this->getLockName($name) . ': process running longer than expected');

        return Command::SUCCESS;
    }

    /**
     * @inheritDoc
     */
    public function getLockName(string $name): string
    {
        return $this->lockPrefix . $name;
    }
}
