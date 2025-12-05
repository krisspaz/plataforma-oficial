<?php

declare(strict_types=1);

namespace App\Application\Parent\Query;

/**
 * Query to search parents
 */
final class SearchParentsQuery
{
    public function __construct(
        public readonly string $query
    ) {}
}
