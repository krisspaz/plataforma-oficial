<?php

declare(strict_types=1);

namespace App\Domain\Student;

use App\Domain\Student\ValueObject\StudentId;
use App\Domain\Student\ValueObject\Email;
use App\Entity\Student;

interface StudentRepositoryInterface
{
    public function save(Student $student): void;

    public function findById(StudentId $id): ?Student;

    public function findByEmail(Email $email): ?Student;

    /**
     * @return Student[]
     */
    public function findAll(): array;

    /**
     * @return Student[]
     */
    public function findByGrade(int $gradeId): array;

    /**
     * @param string $query Search query
     * @return Student[]
     */
    public function search(string $query): array;

    public function delete(Student $student): void;

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return Student[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;

    public function count(array $criteria = []): int;

    public function nextIdentity(): StudentId;
}
