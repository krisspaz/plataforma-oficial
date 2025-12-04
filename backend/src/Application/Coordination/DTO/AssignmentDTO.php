<?php

declare(strict_types=1);

namespace App\Application\Coordination\DTO;

use App\Domain\Coordination\Entity\Assignment;

final class AssignmentDTO
{
    public function __construct(
        public readonly string $id,
        public readonly int $teacherId,
        public readonly string $teacherName,
        public readonly int $subjectId,
        public readonly string $subjectName,
        public readonly int $gradeId,
        public readonly string $gradeName,
        public readonly int $sectionId,
        public readonly string $sectionName,
        public readonly int $academicYear
    ) {}

    public static function fromEntity(Assignment $assignment): self
    {
        $teacher = $assignment->getTeacher();
        $user = $teacher->getUser();
        $teacherName = $user ? sprintf('%s %s', $user->getFirstName(), $user->getLastName()) : 'Unknown';

        return new self(
            (string) $assignment->getId(),
            $teacher->getId(),
            $teacherName,
            $assignment->getSubject()->getId(),
            $assignment->getSubject()->getName(),
            $assignment->getGrade()->getId(),
            $assignment->getGrade()->getName(),
            $assignment->getSection()->getId(),
            $assignment->getSection()->getName(),
            $assignment->getAcademicYear()
        );
    }
}
