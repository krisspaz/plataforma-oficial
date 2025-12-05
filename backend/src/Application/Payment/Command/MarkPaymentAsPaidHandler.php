<?php

declare(strict_types=1);

namespace App\Application\Payment\Command;

use App\Entity\Payment;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class MarkPaymentAsPaidHandler
{
    public function __construct(
        private readonly PaymentRepository $paymentRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger
    ) {}

    public function __invoke(MarkPaymentAsPaidCommand $command): ?Payment
    {
        $payment = $this->paymentRepository->find($command->paymentId);

        if (!$payment) {
            return null;
        }

        $payment->markAsPaid();

        if ($command->paymentMethod) {
            $payment->setPaymentMethod($command->paymentMethod);
        }

        if ($command->receipt) {
            $payment->setReceipt($command->receipt);
        }

        if ($command->metadata) {
            $payment->setMetadata(array_merge(
                $payment->getMetadata() ?? [],
                $command->metadata
            ));
        }

        $this->entityManager->flush();

        $this->logger->info('Payment marked as paid', [
            'payment_id' => $payment->getId(),
            'amount' => $payment->getAmount(),
        ]);

        return $payment;
    }
}
