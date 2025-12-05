<?php

declare(strict_types=1);

namespace App\Application\Chat\Query;

/**
 * Query to get unread messages in a room
 */
final class GetUnreadMessagesQuery
{
    public function __construct(
        public readonly int $roomId,
        public readonly int $userId
    ) {}
}
