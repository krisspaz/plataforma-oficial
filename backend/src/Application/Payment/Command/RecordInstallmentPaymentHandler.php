<?php

declare(strict_types=1);

namespace App\Application\Payment\Command;

use App\Domain\Payment\Entity\Installment;
use App\Domain\Payment\Repository\InstallmentRepositoryInterface;
use App\Entity\AuditLog;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RecordInstallmentPaymentHandler
{
    public function __construct(
        private readonly InstallmentRepositoryInterface $installmentRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function __invoke(RecordInstallmentPaymentCommand $command): Installment
    {
        $installment = $this->installmentRepository->findById($command->getInstallmentUuid());

        if (!$installment) {
            throw new \InvalidArgumentException(
                sprintf('Installment with ID %s not found', $command->installmentId)
            );
        }

        if ($installment->isPaid()) {
            throw new \DomainException('This installment has already been paid');
        }

        // Mark as paid
        $installment->markAsPaid($command->paymentMethod, $command->receiptNumber);

        if ($command->metadata) {
            $installment->setMetadata($command->metadata);
        }

        // Save
        $this->installmentRepository->save($installment);

        // Create audit log
        $this->createAuditLog($installment, $command);

        return $installment;
    }

    private function createAuditLog(Installment $installment, RecordInstallmentPaymentCommand $command): void
    {
        $auditLog = new AuditLog();
        $auditLog->setAction('payment_recorded');
        $auditLog->setEntityType('Installment');
        $auditLog->setEntityId((string) $installment->getId());
        $auditLog->setDetails([
            'amount' => $installment->getAmount()->getValue(),
            'payment_method' => $command->paymentMethod,
            'receipt_number' => $installment->getReceiptNumber(),
            'installment_number' => $installment->getFormattedNumber(),
            'student_id' => $installment->getPaymentPlan()->getEnrollment()->getStudent()->getId(),
        ]);

        if ($command->recordedByUserId) {
            // Set user if tracked
        }

        $this->entityManager->persist($auditLog);
        $this->entityManager->flush();
    }
}
