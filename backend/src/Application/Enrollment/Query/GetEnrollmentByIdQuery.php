<?php

declare(strict_types=1);

namespace App\Application\Enrollment\Query;

/**
 * Query to get a single enrollment by ID
 */
final class GetEnrollmentByIdQuery
{
    public function __construct(
        public readonly int $id
    ) {}
}
