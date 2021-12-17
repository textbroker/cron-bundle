<?php
declare(strict_types=1);

namespace MH1\CronBundle\Service;

use MH1\CronBundle\Command\AbstractCronCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

class CronCommandHelperService implements CronCommandHelperServiceInterface
{
    /**
     * @var string
     */
    private $lockPrefix;

    /**
     * @var LockFactory
     */
    private $lockFactory;

    /**
     * @var LockInterface
     */
    private $lock;

    public function __construct(LockFactory $lockFactory, string $lockPrefix = '')
    {
        $this->lockPrefix = $lockPrefix;
        $this->lockFactory = $lockFactory;
    }

    /**
     * @inheritDoc
     */
    public function trackCommandIsLocked(OutputInterface $output, string $name): int
    {
        $output->writeln('The command is already running in another process.');

        return AbstractCronCommand::SKIPPED;
    }

    /**
     * @inheritDoc
     */
    public function lockCommand(string $name): bool
    {
        $this->lock = $this->lockFactory->createLock($this->getLockName($name));
        return $this->lock->acquire();
    }

    /**
     * @inheritDoc
     */
    public function releaseCommand(): void
    {
        if ($this->lock === null) {
            return;
        }

        $this->lock->release();
    }

    /**
     * @inheritDoc
     */
    public function getLockName(string $name): string
    {
        return $this->lockPrefix . $name;
    }
}
