<?php

declare(strict_types=1);

namespace App\Application\Coordination\Command;

use App\Domain\Coordination\Entity\Assignment;
use App\Domain\Coordination\Repository\AssignmentRepositoryInterface;
use App\Repository\GradeRepository;
use App\Repository\SectionRepository;
use App\Repository\SubjectRepository;
use App\Repository\TeacherRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class AssignTeacherHandler
{
    public function __construct(
        private readonly AssignmentRepositoryInterface $assignmentRepository,
        private readonly TeacherRepository $teacherRepository,
        private readonly SubjectRepository $subjectRepository,
        private readonly GradeRepository $gradeRepository,
        private readonly SectionRepository $sectionRepository
    ) {}

    public function __invoke(AssignTeacherCommand $command): void
    {
        $teacher = $this->teacherRepository->find($command->teacherId);
        $subject = $this->subjectRepository->find($command->subjectId);
        $grade = $this->gradeRepository->find($command->gradeId);
        $section = $this->sectionRepository->find($command->sectionId);

        if (!$teacher || !$subject || !$grade || !$section) {
            throw new \InvalidArgumentException('Invalid entity ID provided');
        }

        // Check if assignment already exists? 
        // For now, we allow multiple assignments, but maybe we should check for duplicates.
        // Let's assume the UI handles basic validation, but domain should prevent exact duplicates.

        $assignment = new Assignment(
            $teacher,
            $subject,
            $grade,
            $section,
            $command->academicYear
        );

        $this->assignmentRepository->save($assignment);
    }
}
