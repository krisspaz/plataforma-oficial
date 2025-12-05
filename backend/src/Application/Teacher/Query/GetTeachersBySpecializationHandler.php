<?php

declare(strict_types=1);

namespace App\Application\Teacher\Query;

use App\Repository\TeacherRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetTeachersBySpecializationHandler
{
    public function __construct(
        private readonly TeacherRepository $teacherRepository
    ) {}

    public function __invoke(GetTeachersBySpecializationQuery $query): array
    {
        return $this->teacherRepository->findBySpecialization($query->specialization);
    }
}
