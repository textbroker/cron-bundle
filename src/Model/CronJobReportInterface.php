<?php
declare(strict_types=1);

namespace MH1\CronBundle\Model;

use DateTimeInterface;

interface CronJobReportInterface
{
    /**
     * @param CronJobInterface  $cronJob
     * @param DateTimeInterface $startTime
     */
    public function __construct(CronJobInterface $cronJob, DateTimeInterface $startTime);

    /**
     * @return CronJobInterface
     */
    public function getCronJob(): CronJobInterface;

    /**
     * @return DateTimeInterface
     */
    public function getStartTime(): DateTimeInterface;

    /**
     * @param DateTimeInterface|null $endTime
     *
     * @return self
     */
    public function setEndTime(?DateTimeInterface $endTime): self;

    /**
     * @return DateTimeInterface|null
     */
    public function getEndTime(): ?DateTimeInterface;

    /**
     * @param int|null $exitCode
     *
     * @return self
     */
    public function setExitCode(?int $exitCode): self;

    /**
     * @return int|null
     */
    public function getExitCode(): ?int;

    /**
     * @param string|null $output
     *
     * @return self
     */
    public function setOutput(?string $output): self;

    /**
     * @return string|null
     */
    public function getOutput(): ?string;

    /**
     * @param int|null $duration
     *
     * @return self
     */
    public function setDuration(?int $duration): self;

    /**
     * @return int|null
     */
    public function getDuration(): ?int;
}
