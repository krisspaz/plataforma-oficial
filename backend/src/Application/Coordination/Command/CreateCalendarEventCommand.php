<?php

declare(strict_types=1);

namespace App\Application\Coordination\Command;

final class CreateCalendarEventCommand
{
    public function __construct(
        public readonly string $title,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly string $type,
        public readonly int $academicYear,
        public readonly bool $isAllDay = false,
        public readonly ?string $description = null
    ) {}
}
