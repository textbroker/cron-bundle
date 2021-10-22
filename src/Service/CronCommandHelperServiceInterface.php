<?php
declare(strict_types=1);

namespace MH1\CronBundle\Service;

use Symfony\Component\Console\Output\OutputInterface;

interface CronCommandHelperServiceInterface
{
    /**
     * @param OutputInterface $output
     * @param string          $name
     *
     * @return int
     */
    public function commandIsLocked(OutputInterface $output, string $name): int;

    /**
     * @param string $name
     *
     * @return string
     */
    public function getLockName(string $name): string;
}
