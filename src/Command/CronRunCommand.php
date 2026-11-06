<?php
declare(strict_types=1);

namespace MH1\CronBundle\Command;

use MH1\CronBundle\Service\CronJobServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mh1:cron:run')]
class CronRunCommand extends Command
{
    /**
     * @var CronJobServiceInterface
     */
    private $cronJobService;

    public function __construct(CronJobServiceInterface $cronJobService)
    {
        parent::__construct();
        $this->cronJobService = $cronJobService;
    }

    protected function configure(): void
    {
        $this->setDescription('runs scheduled cron jobs');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->cronJobService->execute();

        return Command::SUCCESS;
    }
}
