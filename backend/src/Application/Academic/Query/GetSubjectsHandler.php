<?php

declare(strict_types=1);

namespace App\Application\Academic\Query;

use App\Repository\SubjectRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetSubjectsHandler
{
    public function __construct(
        private readonly SubjectRepository $subjectRepository
    ) {}

    public function __invoke(GetSubjectsQuery $query): array
    {
        return $this->subjectRepository->findAll();
    }
}
