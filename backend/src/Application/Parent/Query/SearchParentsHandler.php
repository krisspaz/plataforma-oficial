<?php

declare(strict_types=1);

namespace App\Application\Parent\Query;

use App\Repository\ParentRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SearchParentsHandler
{
    public function __construct(
        private readonly ParentRepository $parentRepository
    ) {}

    public function __invoke(SearchParentsQuery $query): array
    {
        return $this->parentRepository->search($query->query);
    }
}
