<?php
declare(strict_types=1);

namespace MH1\CronBundle\Entity;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use MH1\CronBundle\Model\CronJobInterface;
use MH1\CronBundle\Model\CronJobReportInterface;
use MH1\CronBundle\Repository\Mh1CronJobReportRepository;

/**
 * @ORM\Entity(repositoryClass=Mh1CronJobReportRepository::class)
 */
class Mh1CronJobReport implements CronJobReportInterface
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var CronJobInterface
     * @ORM\ManyToOne(targetEntity=Mh1CronJob::class, inversedBy="cronJobReports", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $cronJob;

    /**
     * @var DateTimeInterface
     * @ORM\Column(type="datetime")
     */
    private $startTime;

    /**
     * @var DateTimeInterface|null
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endTime;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private $exitCode;

    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private $output;

    /**
     * @var int|null
     * @ORM\Column(type="integer", nullable=true)
     */
    private $duration;

    /**
     * @param Mh1CronJob        $cronJob
     * @param DateTimeInterface $startTime
     */
    public function __construct(CronJobInterface $cronJob, DateTimeInterface $startTime)
    {
        $this->cronJob = $cronJob;
        $cronJob->getReports()->add($this)
        ;
        $this->startTime = $startTime;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Mh1CronJobReport
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return CronJobInterface
     */
    public function getCronJob(): CronJobInterface
    {
        return $this->cronJob;
    }

    /**
     * @param CronJobInterface $cronJob
     *
     * @return self
     */
    public function setCronJob(CronJobInterface $cronJob): CronJobReportInterface
    {
        $this->cronJob = $cronJob;
        return $this;
    }

    /**
     * @return DateTimeInterface
     */
    public function getStartTime(): DateTimeInterface
    {
        return $this->startTime;
    }

    /**
     * @param DateTimeInterface $startTime
     *
     * @return Mh1CronJobReport
     */
    public function setStartTime(DateTimeInterface $startTime): CronJobReportInterface
    {
        $this->startTime = $startTime;
        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getEndTime(): ?DateTimeInterface
    {
        return $this->endTime;
    }

    /**
     * @param DateTimeInterface|null $endTime
     *
     * @return CronJobReportInterface
     */
    public function setEndTime(?DateTimeInterface $endTime): CronJobReportInterface
    {
        $this->endTime = $endTime;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getExitCode(): ?int
    {
        return $this->exitCode;
    }

    /**
     * @param int|null $exitCode
     *
     * @return CronJobReportInterface
     */
    public function setExitCode(?int $exitCode): CronJobReportInterface
    {
        $this->exitCode = $exitCode;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getOutput(): ?string
    {
        return $this->output;
    }

    /**
     * @param string|null $output
     *
     * @return CronJobReportInterface
     */
    public function setOutput(?string $output): CronJobReportInterface
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getDuration(): ?int
    {
        return $this->duration;
    }

    /**
     * @param int|null $duration
     *
     * @return Mh1CronJobReport
     */
    public function setDuration(?int $duration): CronJobReportInterface
    {
        $this->duration = $duration;

        return $this;
    }
}
