<?php

declare(strict_types=1);

namespace App\Application\Chat\Query;

/**
 * Query to get chat rooms for a user
 */
final class GetUserRoomsQuery
{
    public function __construct(
        public readonly int $userId
    ) {}
}
