<?php
declare(strict_types=1);

namespace MH1\CronBundle\Model;

/**
 * Interface CronJobInterface
 */
interface CronJobInterface
{
    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * command part after bin/console
     * e.g. "app:fetch:data"
     * e.g. "app:fetch:data -q"
     *
     * @return string
     */
    public function getCommand(): string;

    /**
     * true if cronjob is activated
     *
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * returns crontab like schedule definition e.g "5 * * * *"
     *
     * @return string
     */
    public function getSchedule(): string;
}
