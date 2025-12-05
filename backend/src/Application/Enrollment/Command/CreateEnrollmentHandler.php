<?php

declare(strict_types=1);

namespace App\Application\Enrollment\Command;

use App\Entity\Enrollment;
use App\Repository\EnrollmentRepository;
use App\Repository\StudentRepository;
use App\Repository\SectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreateEnrollmentHandler
{
    public function __construct(
        private readonly EnrollmentRepository $enrollmentRepository,
        private readonly StudentRepository $studentRepository,
        private readonly SectionRepository $sectionRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function __invoke(CreateEnrollmentCommand $command): array
    {
        $student = $this->studentRepository->find($command->studentId);
        if (!$student) {
            return ['error' => 'Student not found', 'code' => 404];
        }

        $section = $this->sectionRepository->find($command->sectionId);
        if (!$section) {
            return ['error' => 'Section not found', 'code' => 404];
        }

        if (!$section->hasAvailableSpace()) {
            return [
                'error' => 'Section is full',
                'code' => 400,
                'details' => [
                    'capacity' => $section->getCapacity(),
                    'current' => $section->getCurrentEnrollmentCount()
                ]
            ];
        }

        $existingEnrollments = $this->enrollmentRepository->findBy([
            'student' => $student,
            'academicYear' => $section->getAcademicYear(),
            'status' => 'active'
        ]);

        if (!empty($existingEnrollments)) {
            return ['error' => 'Student is already enrolled for this academic year', 'code' => 400];
        }

        $enrollment = new Enrollment();
        $enrollment->setStudent($student);
        $enrollment->setGrade($section->getGrade());
        $enrollment->setSection($section);
        $enrollment->setAcademicYear($section->getAcademicYear());
        $enrollment->setStatus('active');
        $enrollment->setEnrollmentDate(new \DateTime());

        $this->entityManager->persist($enrollment);
        $this->entityManager->flush();

        return ['enrollment' => $enrollment, 'code' => 201];
    }
}
