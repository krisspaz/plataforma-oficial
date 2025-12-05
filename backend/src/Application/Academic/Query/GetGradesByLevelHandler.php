<?php

declare(strict_types=1);

namespace App\Application\Academic\Query;

use App\Repository\GradeRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetGradesByLevelHandler
{
    public function __construct(
        private readonly GradeRepository $gradeRepository
    ) {}

    public function __invoke(GetGradesByLevelQuery $query): array
    {
        return $this->gradeRepository->findByLevel($query->level);
    }
}
