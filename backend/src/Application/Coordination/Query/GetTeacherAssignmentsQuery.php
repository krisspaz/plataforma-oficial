<?php

declare(strict_types=1);

namespace App\Application\Coordination\Query;

final class GetTeacherAssignmentsQuery
{
    public function __construct(
        public readonly int $teacherId,
        public readonly ?int $academicYear = null
    ) {}
}
