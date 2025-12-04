<?php

declare(strict_types=1);

namespace App\Application\Coordination\Query;

final class GetCalendarEventsQuery
{
    public function __construct(
        public readonly string $startDate,
        public readonly string $endDate
    ) {}
}
