<?php

declare(strict_types=1);

namespace App\Application\Student\Query;

use App\Domain\Student\StudentRepositoryInterface;
use App\Domain\Student\ValueObject\StudentId;
use App\Entity\Student;

final readonly class GetStudentByIdQueryHandler
{
    public function __construct(
        private StudentRepositoryInterface $studentRepository,
    ) {}

    public function handle(int $id): ?Student
    {
        $studentId = StudentId::fromInt($id);
        return $this->studentRepository->findById($studentId);
    }
}
