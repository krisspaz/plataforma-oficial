<?php

declare(strict_types=1);

namespace App\Application\Parent\Query;

/**
 * Query to get parents by student ID
 */
final class GetParentsByStudentQuery
{
    public function __construct(
        public readonly int $studentId
    ) {}
}
