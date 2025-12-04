<?php

declare(strict_types=1);

namespace App\Application\Coordination\Command;

final class AssignTeacherCommand
{
    public function __construct(
        public readonly int $teacherId,
        public readonly int $subjectId,
        public readonly int $gradeId,
        public readonly int $sectionId,
        public readonly int $academicYear
    ) {}
}
