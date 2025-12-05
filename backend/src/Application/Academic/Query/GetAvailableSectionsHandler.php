<?php

declare(strict_types=1);

namespace App\Application\Academic\Query;

use App\Repository\SectionRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetAvailableSectionsHandler
{
    public function __construct(
        private readonly SectionRepository $sectionRepository
    ) {}

    public function __invoke(GetAvailableSectionsQuery $query): array
    {
        $year = $query->year ?? (int) date('Y');
        return $this->sectionRepository->findWithAvailableSpace($year);
    }
}
