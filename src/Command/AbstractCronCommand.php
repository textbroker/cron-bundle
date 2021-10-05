<?php
declare(strict_types=1);

namespace MH1\CronBundle\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Helper class to block commands from running multiple times in parallel
 * logs error if the command is executed while another instance of this command is running
 */
abstract class AbstractCronCommand extends Command
{
    use LockableTrait;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param LoggerInterface $logger
     * @param string|null     $name
     */
    public function __construct(LoggerInterface $logger, string $name = null)
    {
        parent::__construct($name);
        $this->logger = $logger;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    final protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // if command lock can be acquired => not running currently
        if ($this->lock()) {
            return $this->executeCronCommand($input, $output);
        }

        return $this->commandIsLocked($output);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    abstract protected function executeCronCommand(InputInterface $input, OutputInterface $output): int;

    /**
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function commandIsLocked(OutputInterface $output): int
    {
        $output->writeln('The command is already running in another process.');

        // log an error if the process is running longer than expected through the schedule
        $this->logger->error($this->getName() . ': process running longer than expected');

        return Command::SUCCESS;
    }
}
