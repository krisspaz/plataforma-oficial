<?php

declare(strict_types=1);

namespace App\Application\Payment\Query;

/**
 * Query to get paginated list of payments with optional filters
 */
final class GetPaymentsQuery
{
    public function __construct(
        public readonly ?string $status = null,
        public readonly int $page = 1,
        public readonly int $limit = 20
    ) {}
}
