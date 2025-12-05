<?php

declare(strict_types=1);

namespace App\Application\Chat\Query;

/**
 * Query to get messages in a chat room
 */
final class GetRoomMessagesQuery
{
    public function __construct(
        public readonly int $roomId,
        public readonly int $userId,
        public readonly int $limit = 50
    ) {}
}
