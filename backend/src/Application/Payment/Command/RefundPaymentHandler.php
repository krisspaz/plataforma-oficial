<?php

declare(strict_types=1);

namespace App\Application\Payment\Command;

use App\Entity\Payment;
use App\Repository\PaymentRepository;
use App\Service\Payment\StripePaymentService;
use App\Service\Payment\PayPalPaymentService;
use App\Service\Payment\BACPaymentService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RefundPaymentHandler
{
    public function __construct(
        private readonly PaymentRepository $paymentRepository,
        private readonly StripePaymentService $stripeService,
        private readonly PayPalPaymentService $paypalService,
        private readonly BACPaymentService $bacService,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger
    ) {}

    public function __invoke(RefundPaymentCommand $command): array
    {
        $payment = $this->paymentRepository->find($command->paymentId);

        if (!$payment) {
            throw new \InvalidArgumentException('Payment not found');
        }

        if ($payment->getStatus() !== 'paid') {
            throw new \InvalidArgumentException('Only paid payments can be refunded');
        }

        $metadata = $payment->getMetadata() ?? [];
        $gateway = $metadata['gateway'] ?? null;
        $gatewayPaymentId = $metadata['gateway_payment_id'] ?? null;

        if (!$gateway || !$gatewayPaymentId) {
            throw new \InvalidArgumentException('Payment was not processed through a gateway');
        }

        $amount = $command->amount ?? $payment->getAmount();

        $success = match ($gateway) {
            'stripe' => $this->stripeService->refund($gatewayPaymentId, $amount),
            'paypal' => $this->paypalService->refund($gatewayPaymentId, $amount),
            'bac' => $this->bacService->refund($gatewayPaymentId, $amount),
            default => throw new \InvalidArgumentException('Invalid payment gateway'),
        };

        if ($success) {
            $payment->setStatus('refunded');
            $payment->setMetadata(array_merge($metadata, [
                'refunded_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                'refund_amount' => $amount,
            ]));

            $this->entityManager->flush();

            $this->logger->info('Payment refunded', [
                'payment_id' => $payment->getId(),
                'amount' => $amount,
                'gateway' => $gateway,
            ]);

            return [
                'success' => true,
                'payment' => $payment,
            ];
        }

        return [
            'success' => false,
            'message' => 'Refund failed',
        ];
    }
}
