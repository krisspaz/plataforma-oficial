<?php

declare(strict_types=1);

namespace App\Application\Parent\Query;

use App\Entity\User;

/**
 * Query to get my payments (for authenticated parent)
 */
final class GetMyPaymentsQuery
{
    public function __construct(
        public readonly User $user
    ) {}
}
