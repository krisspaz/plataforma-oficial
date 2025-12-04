<?php

declare(strict_types=1);

namespace App\Application\Coordination\Query;

use App\Application\Coordination\DTO\AssignmentDTO;
use App\Domain\Coordination\Repository\AssignmentRepositoryInterface;
use App\Repository\TeacherRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetTeacherAssignmentsHandler
{
    public function __construct(
        private readonly AssignmentRepositoryInterface $assignmentRepository,
        private readonly TeacherRepository $teacherRepository
    ) {}

    public function __invoke(GetTeacherAssignmentsQuery $query): array
    {
        $teacher = $this->teacherRepository->find($query->teacherId);

        if (!$teacher) {
            throw new \InvalidArgumentException('Teacher not found');
        }

        if ($query->academicYear) {
            $assignments = $this->assignmentRepository->findByTeacher($teacher, $query->academicYear);
        } else {
            $assignments = $this->assignmentRepository->findActiveByTeacher($teacher);
        }

        return array_map(
            fn($assignment) => AssignmentDTO::fromEntity($assignment),
            $assignments
        );
    }
}
