<?php
declare(strict_types=1);

namespace MH1\CronBundle\Command;

use MH1\CronBundle\Service\CronJobServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CronRunCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'mh1:cron:run';

    /**
     * @var CronJobServiceInterface
     */
    private $cronJobService;

    public function __construct(CronJobServiceInterface $cronJobService)
    {
        parent::__construct(self::$defaultName);
        $this->cronJobService = $cronJobService;
    }

    protected function configure(): void
    {
        $this->setName(self::$defaultName)
             ->setDescription('runs scheduled cron jobs')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->cronJobService->execute();

        return Command::SUCCESS;
    }
}
