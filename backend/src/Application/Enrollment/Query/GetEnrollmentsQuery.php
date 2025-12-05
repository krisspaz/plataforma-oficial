<?php

declare(strict_types=1);

namespace App\Application\Enrollment\Query;

/**
 * Query to get enrollments by academic year
 */
final class GetEnrollmentsQuery
{
    public function __construct(
        public readonly int $year
    ) {}
}
