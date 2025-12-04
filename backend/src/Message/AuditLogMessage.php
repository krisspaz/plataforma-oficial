<?php

declare(strict_types=1);

namespace App\Message;

final readonly class AuditLogMessage
{
    public function __construct(
        public string $action,
        public string $userId,
        public array $data = [],
        public \DateTimeImmutable $timestamp = new \DateTimeImmutable(),
    ) {}
}
