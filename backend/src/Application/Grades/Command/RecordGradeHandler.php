<?php

declare(strict_types=1);

namespace App\Application\Grades\Command;

use App\Domain\Grades\Entity\GradeRecord;
use App\Domain\Grades\Repository\GradeRecordRepositoryInterface;
use App\Infrastructure\Cache\CacheService;
use App\Repository\StudentRepository;
use App\Repository\SubjectRepository;
use App\Repository\TeacherRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RecordGradeHandler
{
    public function __construct(
        private readonly GradeRecordRepositoryInterface $gradeRepository,
        private readonly StudentRepository $studentRepository,
        private readonly SubjectRepository $subjectRepository,
        private readonly TeacherRepository $teacherRepository,
        private readonly CacheService $cache
    ) {}

    public function __invoke(RecordGradeCommand $command): void
    {
        $student = $this->studentRepository->find($command->studentId);
        $subject = $this->subjectRepository->find($command->subjectId);
        $teacher = $this->teacherRepository->find($command->teacherId);

        if (!$student || !$subject || !$teacher) {
            throw new \InvalidArgumentException('Invalid student, subject, or teacher ID');
        }

        // Check if existing record exists (update case)
        $existingRecord = $this->gradeRepository->findOne(
            $student,
            $subject,
            $command->bimester,
            $command->academicYear
        );

        if ($existingRecord) {
            $existingRecord->updateGrade($command->grade, $teacher, $command->comments);
            $this->gradeRepository->save($existingRecord);
        } else {
            // Create new record
            $record = new GradeRecord(
                $student,
                $subject,
                $teacher,
                $command->bimester,
                $command->academicYear,
                $command->grade,
                $command->comments
            );
            $this->gradeRepository->save($record);
        }

        // Invalidate cache for this student
        $this->cache->invalidateStudentGrades($command->studentId);
    }
}
