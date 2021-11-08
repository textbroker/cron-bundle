<?php
declare(strict_types=1);

namespace MH1\CronBundle\Service;

use MH1\CronBundle\Command\AbstractCronCommand;
use Symfony\Component\Console\Output\OutputInterface;

class CronCommandHelperService implements CronCommandHelperServiceInterface
{
    /**
     * @var string
     */
    private $lockPrefix;

    public function __construct(string $lockPrefix = '')
    {
        $this->lockPrefix = $lockPrefix;
    }

    /**
     * @inheritDoc
     */
    public function commandIsLocked(OutputInterface $output, string $name): int
    {
        $output->writeln('The command is already running in another process.');

        return AbstractCronCommand::SKIPPED;
    }

    /**
     * @inheritDoc
     */
    public function getLockName(string $name): string
    {
        return $this->lockPrefix . $name;
    }
}
