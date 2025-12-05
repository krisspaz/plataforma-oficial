<?php

declare(strict_types=1);

namespace App\Application\Academic\Query;

use App\Repository\SectionRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetSectionByIdHandler
{
    public function __construct(
        private readonly SectionRepository $sectionRepository
    ) {}

    public function __invoke(GetSectionByIdQuery $query): ?array
    {
        $section = $this->sectionRepository->find($query->id);

        if (!$section) {
            return null;
        }

        return [
            'section' => $section,
            'currentEnrollment' => $section->getCurrentEnrollmentCount(),
            'hasSpace' => $section->hasAvailableSpace()
        ];
    }
}
