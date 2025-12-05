<?php

declare(strict_types=1);

namespace App\Application\Teacher\Query;

use App\Repository\TeacherRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SearchTeachersHandler
{
    public function __construct(
        private readonly TeacherRepository $teacherRepository
    ) {}

    public function __invoke(SearchTeachersQuery $query): array
    {
        return $this->teacherRepository->search($query->query);
    }
}
