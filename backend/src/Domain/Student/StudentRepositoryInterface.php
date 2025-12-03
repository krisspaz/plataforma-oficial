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

    public function nextIdentity(): StudentId;
}
