<?php

declare(strict_types=1);

namespace App\Application\Coordination\Query;

use App\Application\Coordination\DTO\CalendarEventDTO;
use App\Domain\Coordination\Repository\CalendarEventRepositoryInterface;
use DateTimeImmutable;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetCalendarEventsHandler
{
    public function __construct(
        private readonly CalendarEventRepositoryInterface $eventRepository
    ) {}

    public function __invoke(GetCalendarEventsQuery $query): array
    {
        $start = new DateTimeImmutable($query->startDate);
        $end = new DateTimeImmutable($query->endDate);

        $events = $this->eventRepository->findBetween($start, $end);

        return array_map(
            fn($event) => CalendarEventDTO::fromEntity($event),
            $events
        );
    }
}
