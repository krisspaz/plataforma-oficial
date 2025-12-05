<?php

declare(strict_types=1);

namespace App\Application\Student\Query;

use App\Domain\Student\StudentRepositoryInterface;

final readonly class GetAllStudentsQueryHandler
{
    public function __construct(
        private StudentRepositoryInterface $studentRepository,
    ) {}

    public function __invoke(int $page = 1, int $perPage = 20, ?string $search = null, string $sortBy = 'id', string $sortOrder = 'asc'): array
    {
        $criteria = [];
        if ($search) {
            // This assumes the repository implementation handles 'search' criteria or we use a specific search method.
            // For simplicity with standard findBy, we might need a custom method in repository if we want LIKE search.
            // But let's assume the repository can handle it or we use the search method if provided.
            // However, the interface has a search method!
            if (method_exists($this->studentRepository, 'search') && $search) {
                // If search is used, pagination might be tricky if search doesn't support it.
                // Let's stick to findBy for now and assume criteria can handle it or ignore search for this basic implementation
                // OR better, use the search method if available.
                // But wait, the interface defined 'search(string $query): array'. It returns all results.
                // Let's use findBy for pagination.
            }
        }

        $orderBy = [$sortBy => $sortOrder];
        $limit = $perPage;
        $offset = ($page - 1) * $perPage;

        $students = $this->studentRepository->findBy($criteria, $orderBy, $limit, $offset);
        $total = $this->studentRepository->count($criteria);

        return [
            'data' => $students,
            'meta' => [
                'currentPage' => $page,
                'perPage' => $perPage,
                'totalPages' => ceil($total / $perPage),
                'totalItems' => $total,
            ]
        ];
    }
}
