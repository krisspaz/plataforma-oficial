<?php

declare(strict_types=1);

namespace App\Domain\Student;

use App\Entity\Student;

interface StudentRepositoryInterface
{
    public function findByEmail(string $email): ?Student;

    /**
     * @return Student[]
     */
    public function findAll(): array;

    /**
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return Student[]
     */
    public function findBy(array $criteria = [], ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array;

    public function count(array $criteria = []): int;

    public function save(Student $student): void;

    public function remove(Student $student): void;
}
