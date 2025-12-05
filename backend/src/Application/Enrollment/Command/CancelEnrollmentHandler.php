<?php

declare(strict_types=1);

namespace App\Application\Enrollment\Command;

use App\Entity\Enrollment;
use App\Repository\EnrollmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CancelEnrollmentHandler
{
    public function __construct(
        private readonly EnrollmentRepository $enrollmentRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function __invoke(CancelEnrollmentCommand $command): ?Enrollment
    {
        $enrollment = $this->enrollmentRepository->find($command->enrollmentId);
        if (!$enrollment) {
            return null;
        }

        $enrollment->setStatus('cancelled');
        $this->entityManager->flush();

        return $enrollment;
    }
}
