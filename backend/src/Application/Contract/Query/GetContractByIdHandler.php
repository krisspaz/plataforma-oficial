<?php

declare(strict_types=1);

namespace App\Application\Contract\Query;

use App\Entity\Contract;
use App\Repository\ContractRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetContractByIdHandler
{
    public function __construct(
        private readonly ContractRepository $contractRepository
    ) {}

    public function __invoke(GetContractByIdQuery $query): ?Contract
    {
        return $this->contractRepository->find($query->id);
    }
}
