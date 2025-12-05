<?php

declare(strict_types=1);

namespace App\Application\Chat\Command;

/**
 * Command to create a chat room
 */
final class CreateRoomCommand
{
    public function __construct(
        public readonly int $userId,
        public readonly int $participantId,
        public readonly ?string $name = null
    ) {}
}
