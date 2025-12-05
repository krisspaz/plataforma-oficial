<?php

declare(strict_types=1);

namespace App\Application\Academic\Query;

use App\Repository\SectionRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetSectionsHandler
{
    public function __construct(
        private readonly SectionRepository $sectionRepository
    ) {}

    public function __invoke(GetSectionsQuery $query): array
    {
        $year = $query->year ?? (int) date('Y');

        if ($query->gradeId) {
            return $this->sectionRepository->findByGradeAndYear($query->gradeId, $year);
        }

        return $this->sectionRepository->findAll();
    }
}
