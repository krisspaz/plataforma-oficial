<?php

declare(strict_types=1);

namespace App\Domain\Coordination\Repository;

use App\Domain\Coordination\Entity\CalendarEvent;
use DateTimeImmutable;

interface CalendarEventRepositoryInterface
{
    public function save(CalendarEvent $event): void;
    public function remove(CalendarEvent $event): void;
    public function findBetween(DateTimeImmutable $start, DateTimeImmutable $end): array;
    public function findUpcoming(int $limit = 5): array;
}
