<?php

declare(strict_types=1);

namespace App\Application\Teacher\Query;

/**
 * Query to search teachers
 */
final class SearchTeachersQuery
{
    public function __construct(
        public readonly string $query
    ) {}
}
