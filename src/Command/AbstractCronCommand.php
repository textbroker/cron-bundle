<?php
declare(strict_types=1);

namespace MH1\CronBundle\Command;

use MH1\CronBundle\Service\CronCommandHelperServiceInterface;
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
     * @var CronCommandHelperServiceInterface
     */
    protected $helperService;

    /**
     * @param CronCommandHelperServiceInterface $helperService
     * @param string|null                       $name
     */
    public function __construct(CronCommandHelperServiceInterface $helperService, string $name = null)
    {
        parent::__construct($name);
        $this->helperService = $helperService;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    final protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $this->getName() ?? '';
        // if command lock can be acquired => not running currently
        if ($this->lock($this->helperService->getLockName($name))) {
            return $this->executeCronCommand($input, $output);
        }

        return $this->helperService->commandIsLocked($output, $name);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    abstract protected function executeCronCommand(InputInterface $input, OutputInterface $output): int;
}
