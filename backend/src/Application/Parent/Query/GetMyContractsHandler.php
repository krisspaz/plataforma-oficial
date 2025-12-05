<?php

declare(strict_types=1);

namespace App\Application\Parent\Query;

use App\Repository\ParentRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetMyContractsHandler
{
    public function __construct(
        private readonly ParentRepository $parentRepository
    ) {}

    public function __invoke(GetMyContractsQuery $query): ?array
    {
        $parent = $this->parentRepository->findOneBy(['user' => $query->user]);
        if (!$parent) {
            return null;
        }

        $contracts = $parent->getContracts();

        return [
            'contracts' => $contracts,
            'count' => count($contracts)
        ];
    }
}
