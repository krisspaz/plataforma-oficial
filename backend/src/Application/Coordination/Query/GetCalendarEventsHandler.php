<?php

declare(strict_types=1);

namespace App\Application\Coordination\Query;

use App\Application\Coordination\DTO\CalendarEventDTO;
use App\Domain\Coordination\Repository\CalendarEventRepositoryInterface;
use App\Infrastructure\Cache\CacheService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetCalendarEventsHandler
{
    public function __construct(
        private readonly CalendarEventRepositoryInterface $repository,
        private readonly CacheService $cache
    ) {}

    public function __invoke(GetCalendarEventsQuery $query): array
    {
        return $this->cache->getCalendarEvents(
            $query->startDate->format('Y-m-d'),
            $query->endDate->format('Y-m-d'),
            fn() => array_map(
                fn($event) => CalendarEventDTO::fromEntity($event),
                $this->repository->findByDateRange($query->startDate, $query->endDate)
            )
        );
    }
}
