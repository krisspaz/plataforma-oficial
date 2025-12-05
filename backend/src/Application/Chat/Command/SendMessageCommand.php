<?php

declare(strict_types=1);

namespace App\Application\Chat\Command;

use App\Entity\User;

/**
 * Command to send a message in a chat room
 */
final class SendMessageCommand
{
    public function __construct(
        public readonly int $roomId,
        public readonly User $sender,
        public readonly string $content,
        public readonly ?array $attachments = null
    ) {}
}
