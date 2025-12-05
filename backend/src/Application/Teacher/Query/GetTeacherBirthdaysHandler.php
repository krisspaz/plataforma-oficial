<?php

declare(strict_types=1);

namespace App\Application\Teacher\Query;

use App\Repository\TeacherRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetTeacherBirthdaysHandler
{
    public function __construct(
        private readonly TeacherRepository $teacherRepository
    ) {}

    public function __invoke(GetTeacherBirthdaysQuery $query): array
    {
        $teachers = $this->teacherRepository->findBirthdaysThisMonth();

        return [
            'teachers' => $teachers,
            'count' => count($teachers)
        ];
    }
}
