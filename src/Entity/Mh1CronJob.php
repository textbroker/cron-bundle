<?php
declare(strict_types=1);

namespace MH1\CronBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use MH1\CronBundle\Model\CronJobInterface;
use MH1\CronBundle\Repository\Mh1CronJobRepository;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass=Mh1CronJobRepository::class)
 */
class Mh1CronJob implements CronJobInterface
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $command;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $schedule;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $executeStalled;

    /**
     * @var Collection<int, Mh1CronJobReport>
     *
     * @ORM\OneToMany(
     *     targetEntity=Mh1CronJobReport::class,
     *     mappedBy="cronJob",
     *     orphanRemoval=true,
     *     cascade={"persist", "remove"}
     * )
     */
    private $reports;

    public function __construct()
    {
        $this->reports = new ArrayCollection();
    }

    /**
     * @return UuidInterface
     */
    public function getId(): UuidInterface
    {
        return $this->id;
    }

    /**
     * @param UuidInterface $id
     *
     * @return Mh1CronJob
     */
    public function setId(UuidInterface $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return Mh1CronJob
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * @param string $command
     *
     * @return Mh1CronJob
     */
    public function setCommand(string $command): self
    {
        $this->command = $command;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     *
     * @return Mh1CronJob
     */
    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return string
     */
    public function getSchedule(): string
    {
        return $this->schedule;
    }

    /**
     * @param string $schedule
     *
     * @return Mh1CronJob
     */
    public function setSchedule(string $schedule): self
    {
        $this->schedule = $schedule;
        return $this;
    }

    /**
     * @return Collection<int, Mh1CronJobReport>
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     *
     * @return Mh1CronJob
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return bool
     */
    public function isExecuteStalled(): bool
    {
        return $this->executeStalled;
    }

    /**
     * @param bool $executeStalled
     *
     * @return Mh1CronJob
     */
    public function setExecuteStalled(bool $executeStalled): self
    {
        $this->executeStalled = $executeStalled;

        return $this;
    }
}
