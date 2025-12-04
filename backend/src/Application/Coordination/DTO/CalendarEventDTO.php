<?php

declare(strict_types=1);

namespace App\Application\Coordination\DTO;

use App\Domain\Coordination\Entity\CalendarEvent;

final class CalendarEventDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly ?string $description,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly string $type,
        public readonly bool $isAllDay
    ) {}

    public static function fromEntity(CalendarEvent $event): self
    {
        return new self(
            (string) $event->getId(),
            $event->getTitle(),
            $event->getDescription(),
            $event->getStartDate()->format('Y-m-d H:i:s'),
            $event->getEndDate()->format('Y-m-d H:i:s'),
            $event->getType(),
            $event->isAllDay()
        );
    }
}
