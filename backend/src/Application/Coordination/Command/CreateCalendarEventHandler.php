<?php

declare(strict_types=1);

namespace App\Application\Coordination\Command;

use App\Domain\Coordination\Entity\CalendarEvent;
use App\Domain\Coordination\Repository\CalendarEventRepositoryInterface;
use DateTimeImmutable;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreateCalendarEventHandler
{
    public function __construct(
        private readonly CalendarEventRepositoryInterface $eventRepository
    ) {}

    public function __invoke(CreateCalendarEventCommand $command): void
    {
        $startDate = new DateTimeImmutable($command->startDate);
        $endDate = new DateTimeImmutable($command->endDate);

        if ($endDate < $startDate) {
            throw new \InvalidArgumentException('End date cannot be before start date');
        }

        $event = new CalendarEvent(
            $command->title,
            $startDate,
            $endDate,
            $command->type,
            $command->academicYear,
            $command->isAllDay,
            $command->description
        );

        $this->eventRepository->save($event);
    }
}
