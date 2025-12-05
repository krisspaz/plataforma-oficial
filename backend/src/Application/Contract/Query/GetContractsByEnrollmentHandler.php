<?php

declare(strict_types=1);

namespace App\Application\Contract\Query;

use App\Repository\ContractRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetContractsByEnrollmentHandler
{
    public function __construct(
        private readonly ContractRepository $contractRepository
    ) {}

    public function __invoke(GetContractsByEnrollmentQuery $query): array
    {
        return $this->contractRepository->findByEnrollment($query->enrollmentId);
    }
}
