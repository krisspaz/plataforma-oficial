<?php

declare(strict_types=1);

namespace App\Application\Grades\Query;

final class GetStudentGradesQuery
{
    public function __construct(
        public readonly int $studentId,
        public readonly ?int $bimester = null,
        public readonly ?int $academicYear = null
    ) {}
}
