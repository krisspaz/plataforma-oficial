<?php

declare(strict_types=1);

namespace App\Application\Contract\Query;

use App\Repository\ContractRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetContractsHandler
{
    public function __construct(
        private readonly ContractRepository $contractRepository
    ) {}

    public function __invoke(GetContractsQuery $query): array
    {
        return $query->status
            ? $this->contractRepository->findByStatus($query->status)
            : $this->contractRepository->findAll();
    }
}
