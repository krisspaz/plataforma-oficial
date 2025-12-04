<?php

declare(strict_types=1);

namespace App\Application\Grades\DTO;

use App\Domain\Grades\Entity\GradeRecord;

final class GradeRecordDTO
{
    public function __construct(
        public readonly string $id,
        public readonly int $studentId,
        public readonly string $studentName,
        public readonly int $subjectId,
        public readonly string $subjectName,
        public readonly int $bimester,
        public readonly string $bimesterName,
        public readonly float $grade,
        public readonly string $letterGrade,
        public readonly bool $isPassing,
        public readonly ?string $comments,
        public readonly string $recordedAt,
        public readonly string $teacherName
    ) {}

    public static function fromEntity(GradeRecord $record): self
    {
        $student = $record->getStudent();
        $studentUser = $student->getUser();
        $studentName = $studentUser ? sprintf('%s %s', $studentUser->getFirstName(), $studentUser->getLastName()) : 'Unknown';

        $teacher = $record->getRecordedBy();
        $teacherUser = $teacher->getUser();
        $teacherName = $teacherUser ? sprintf('%s %s', $teacherUser->getFirstName(), $teacherUser->getLastName()) : 'Unknown';

        return new self(
            id: (string) $record->getId(),
            studentId: $student->getId(),
            studentName: $studentName,
            subjectId: $record->getSubject()->getId(),
            subjectName: $record->getSubject()->getName(),
            bimester: $record->getBimester(),
            bimesterName: $record->getBimesterName(),
            grade: $record->getGrade(),
            letterGrade: $record->getLetterGrade(),
            isPassing: $record->isPassing(),
            comments: $record->getComments(),
            recordedAt: $record->getRecordedAt()->format('Y-m-d H:i:s'),
            teacherName: $teacherName
        );
    }
}
