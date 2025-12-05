<?php

declare(strict_types=1);

namespace App\Application\Contract\Query;

/**
 * Query to get contracts with optional status filter
 */
final class GetContractsQuery
{
    public function __construct(
        public readonly ?string $status = null
    ) {}
}
