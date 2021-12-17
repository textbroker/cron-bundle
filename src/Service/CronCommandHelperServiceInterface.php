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
    public function trackCommandIsLocked(OutputInterface $output, string $name): int;

    /**
     * @param string $name
     *
     * @return bool
     */
    public function lockCommand(string $name): bool;

    /**
     * @return void
     */
    public function releaseCommand(): void;

    /**
     * @param string $name
     *
     * @return string
     */
    public function getLockName(string $name): string;
}
