<?php

declare(strict_types=1);

namespace App\Application\Chat\Command;

/**
 * Command to mark a message as read
 */
final class MarkMessageAsReadCommand
{
    public function __construct(
        public readonly int $messageId,
        public readonly int $userId
    ) {}
}
