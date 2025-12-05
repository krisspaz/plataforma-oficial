<?php

declare(strict_types=1);

namespace App\Application\Enrollment\Query;

/**
 * Query to get enrollments by student ID
 */
final class GetEnrollmentsByStudentQuery
{
    public function __construct(
        public readonly int $studentId
    ) {}
}
