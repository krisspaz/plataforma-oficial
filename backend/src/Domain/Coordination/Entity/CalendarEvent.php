<?php

declare(strict_types=1);

namespace App\Domain\Coordination\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'calendar_events')]
class CalendarEvent
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $startDate;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $endDate;

    #[ORM\Column(length: 50)]
    private string $type; // 'holiday', 'exam', 'activity', 'meeting'

    #[ORM\Column(type: 'boolean')]
    private bool $isAllDay;

    #[ORM\Column(type: 'integer')]
    private int $academicYear;

    public function __construct(
        string $title,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        string $type,
        int $academicYear,
        bool $isAllDay = false,
        ?string $description = null
    ) {
        $this->id = Uuid::v7();
        $this->title = $title;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->type = $type;
        $this->academicYear = $academicYear;
        $this->isAllDay = $isAllDay;
        $this->description = $description;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getStartDate(): DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isAllDay(): bool
    {
        return $this->isAllDay;
    }

    public function getAcademicYear(): int
    {
        return $this->academicYear;
    }
}
