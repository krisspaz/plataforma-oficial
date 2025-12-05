<?php

declare(strict_types=1);

namespace App\Application\Student\Command;

use App\Domain\Exception\StudentNotFoundException;
use App\Domain\Student\StudentRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DeleteStudentCommandHandler
{
    public function __construct(
        private StudentRepositoryInterface $studentRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    public function __invoke(int $studentId): void
    {
        $student = $this->studentRepository->findById($studentId);

        if (!$student) {
            throw StudentNotFoundException::withId($studentId);
        }

        // Soft delete: marcar como inactivo en lugar de eliminar
        $user = $student->getUser();
        $user->setIsActive(false);

        $this->entityManager->flush();
    }
}
