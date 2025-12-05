<?php

declare(strict_types=1);

namespace App\Application\Academic\Query;

use App\Entity\Grade;
use App\Repository\GradeRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetGradeByIdHandler
{
    public function __construct(
        private readonly GradeRepository $gradeRepository
    ) {}

    public function __invoke(GetGradeByIdQuery $query): ?Grade
    {
        return $this->gradeRepository->find($query->id);
    }
}
