<?php

declare(strict_types=1);

namespace App\Message;

final readonly class DomainEventMessage
{
    public function __construct(
        public string $eventType,
        public array $payload,
        public \DateTimeImmutable $occurredAt = new \DateTimeImmutable(),
    ) {}
}
