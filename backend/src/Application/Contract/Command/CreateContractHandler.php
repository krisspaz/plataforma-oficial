<?php

declare(strict_types=1);

namespace App\Application\Contract\Command;

use App\Repository\EnrollmentRepository;
use App\Repository\ParentRepository;
use App\Service\ContractService;
use App\Service\NotificationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreateContractHandler
{
    public function __construct(
        private readonly EnrollmentRepository $enrollmentRepository,
        private readonly ParentRepository $parentRepository,
        private readonly ContractService $contractService,
        private readonly NotificationService $notificationService
    ) {}

    public function __invoke(CreateContractCommand $command): array
    {
        $enrollment = $this->enrollmentRepository->find($command->enrollmentId);
        if (!$enrollment) {
            return ['error' => 'Enrollment not found', 'code' => 404];
        }

        $parent = $this->parentRepository->find($command->parentId);
        if (!$parent) {
            return ['error' => 'Parent not found', 'code' => 404];
        }

        $contract = $this->contractService->generateContract(
            $enrollment,
            $parent,
            $command->totalAmount,
            $command->installments
        );

        $this->notificationService->createNotification(
            $contract->getParent()->getUser(),
            'Contrato',
            "Se ha generado el contrato {$contract->getContractNumber()}. Por favor revíselo y fírmelo.",
            'contract',
            ['contractId' => $contract->getId(), 'contractNumber' => $contract->getContractNumber()]
        );

        return ['contract' => $contract, 'code' => 201];
    }
}
