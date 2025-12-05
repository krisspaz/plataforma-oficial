<?php

declare(strict_types=1);

namespace App\Application\Teacher\Query;

use App\Entity\Teacher;
use App\Repository\TeacherRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetTeacherByIdHandler
{
    public function __construct(
        private readonly TeacherRepository $teacherRepository
    ) {}

    public function __invoke(GetTeacherByIdQuery $query): ?Teacher
    {
        return $this->teacherRepository->find($query->id);
    }
}
