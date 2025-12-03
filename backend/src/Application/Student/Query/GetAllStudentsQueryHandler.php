<?php

declare(strict_types=1);

namespace App\Application\Student\Query;

use App\Domain\Student\StudentRepositoryInterface;

final readonly class GetAllStudentsQueryHandler
{
    public function __construct(
        private StudentRepositoryInterface $studentRepository,
    ) {}

    /**
     * @return array{students: array, total: int, page: int, perPage: int}
     */
    public function handle(int $page = 1, int $perPage = 20): array
    {
        $students = $this->studentRepository->findAll();
        $total = count($students);

        // Simple pagination (in production, use database-level pagination)
        $offset = ($page - 1) * $perPage;
        $paginatedStudents = array_slice($students, $offset, $perPage);

        return [
            'students' => $paginatedStudents,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int) ceil($total / $perPage),
        ];
    }
}
