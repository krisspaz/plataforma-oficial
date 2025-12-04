<?php

declare(strict_types=1);

namespace App\Application\Grades\Command;

final class RecordGradeCommand
{
    public function __construct(
        public readonly int $studentId,
        public readonly int $subjectId,
        public readonly int $teacherId,
        public readonly int $bimester,
        public readonly int $academicYear,
        public readonly float $grade,
        public readonly ?string $comments = null
    ) {}
}
