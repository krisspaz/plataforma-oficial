<?php

declare(strict_types=1);

namespace App\Application\Enrollment\Command;

use App\Entity\Enrollment;
use App\Repository\EnrollmentRepository;
use App\Repository\SectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UpdateEnrollmentHandler
{
    public function __construct(
        private readonly EnrollmentRepository $enrollmentRepository,
        private readonly SectionRepository $sectionRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function __invoke(UpdateEnrollmentCommand $command): ?Enrollment
    {
        $enrollment = $this->enrollmentRepository->find($command->enrollmentId);
        if (!$enrollment) {
            return null;
        }

        if ($command->status !== null) {
            $enrollment->setStatus($command->status);
        }

        if ($command->sectionId !== null) {
            $section = $this->sectionRepository->find($command->sectionId);
            if ($section) {
                $enrollment->setSection($section);
                $enrollment->setGrade($section->getGrade());
            }
        }

        $this->entityManager->flush();

        return $enrollment;
    }
}
