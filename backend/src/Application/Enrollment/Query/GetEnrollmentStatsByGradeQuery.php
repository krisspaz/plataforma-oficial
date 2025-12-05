<?php

declare(strict_types=1);

namespace App\Application\Enrollment\Query;

/**
 * Query to get enrollment statistics by grade
 */
final class GetEnrollmentStatsByGradeQuery
{
    public function __construct(
        public readonly int $year
    ) {}
}
