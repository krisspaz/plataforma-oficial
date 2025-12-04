<?php

declare(strict_types=1);

namespace App\Domain\Student\ValueObject;

use InvalidArgumentException;

final readonly class StudentId
{
    private int $id;

    public function __construct(int $id)
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('Student ID must be a positive integer.');
        }
        $this->id = $id;
    }

    public static function fromInt(int $id): self
    {
        return new self($id);
    }

    public function toInt(): int
    {
        return $this->id;
    }
}


namespace App\Domain\Student;

use App\Domain\Student\ValueObject\StudentId;
use App\Entity\Student;

interface StudentRepositoryInterface
{
    public function findById(StudentId|int $id): ?Student;
    public function findAll(): array;
    public function save(Student $student): void;
}



namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Student\StudentRepositoryInterface;
use App\Domain\Student\ValueObject\StudentId;
use App\Entity\Student;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

final class DoctrineStudentRepository implements StudentRepositoryInterface
{
    private ObjectRepository $repository;

    public function __construct(private EntityManagerInterface $em)
    {
        $this->repository = $em->getRepository(Student::class);
    }

    public function findById(StudentId|int $id): ?Student
    {
        $idValue = $id instanceof StudentId ? $id->toInt() : $id;
        return $this->repository->find($idValue);
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function save(Student $student): void
    {
        $this->em->persist($student);
        $this->em->flush();
    }
}


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
