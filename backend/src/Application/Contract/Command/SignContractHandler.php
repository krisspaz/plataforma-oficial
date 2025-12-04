<?php

declare(strict_types=1);

namespace App\Application\Contract\Command;

use App\Repository\ContractRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SignContractHandler
{
    public function __construct(
        private readonly ContractRepository $contractRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function __invoke(SignContractCommand $command): void
    {
        $contract = $this->contractRepository->find($command->contractId);

        if (!$contract) {
            throw new \InvalidArgumentException(
                sprintf('Contract with ID %d not found', $command->contractId)
            );
        }

        if ($contract->isSigned()) {
            throw new \DomainException('Contract is already signed');
        }

        // Get signature data
        $signatureData = $command->getSignatureData();

        // Merge with additional metadata
        if ($command->metadata) {
            $signatureData = $signatureData->withMetadata($command->metadata);
        }

        // Mark contract as signed
        $contract->markAsSigned($signatureData->toArray());

        $this->entityManager->flush();
    }
}
