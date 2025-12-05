<?php

declare(strict_types=1);

namespace App\Application\Parent\Query;

use App\Repository\ParentRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetParentsHandler
{
    public function __construct(
        private readonly ParentRepository $parentRepository
    ) {}

    public function __invoke(GetParentsQuery $query): array
    {
        return $this->parentRepository->findAll();
    }
}
