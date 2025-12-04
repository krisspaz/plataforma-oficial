<?php

declare(strict_types=1);

namespace App\Application\Coordination\Command;

use App\Domain\Coordination\Entity\CalendarEvent;
use App\Domain\Coordination\Repository\CalendarEventRepositoryInterface;
use App\Infrastructure\Cache\CacheService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreateCalendarEventHandler
{
    public function __construct(
        private readonly CalendarEventRepositoryInterface $repository,
        private readonly CacheService $cache
    ) {}

    public function __invoke(CreateCalendarEventCommand $command): void
    {
        $event = new CalendarEvent(
            $command->title,
            $command->startDate,
            $command->endDate,
            $command->type,
            $command->description,
            $command->isAllDay
        );

        $this->repository->save($event);

        // Invalidate calendar cache
        $this->cache->invalidateCalendar();
    }
}
