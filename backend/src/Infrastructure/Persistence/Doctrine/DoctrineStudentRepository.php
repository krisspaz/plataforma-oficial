<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Student\StudentRepositoryInterface;
use App\Domain\Student\ValueObject\StudentId;
use App\Domain\Student\ValueObject\Email;
use App\Entity\Student;
use App\Repository\StudentRepository as DoctrineStudentRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineStudentRepository implements StudentRepositoryInterface
{
    public function __construct(
        private DoctrineStudentRepository $doctrineRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    public function save(Student $student): void
    {
        $this->entityManager->persist($student);
        $this->entityManager->flush();
    }

    public function findById(StudentId $id): ?Student
    {
        return $this->doctrineRepository->find($id->value);
    }

    public function findByEmail(Email $email): ?Student
    {
        return $this->doctrineRepository->createQueryBuilder('s')
            ->join('s.user', 'u')
            ->where('u.email = :email')
            ->setParameter('email', (string) $email)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAll(): array
    {
        return $this->doctrineRepository->findAll();
    }

    public function findByGrade(int $gradeId): array
    {
        return $this->doctrineRepository->findByGrade($gradeId);
    }

    public function search(string $query): array
    {
        return $this->doctrineRepository->search($query);
    }

    public function delete(Student $student): void
    {
        $this->entityManager->remove($student);
        $this->entityManager->flush();
    }

    public function nextIdentity(): StudentId
    {
        // This would typically use a sequence or UUID generator
        // For now, we'll let the database auto-increment handle it
        return StudentId::fromInt(0); // Placeholder
    }
}
