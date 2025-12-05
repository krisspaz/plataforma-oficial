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
final class ProcessPaymentHandler
{
    public function __construct(
        private readonly PaymentRepository $paymentRepository,
        private readonly StripePaymentService $stripeService,
        private readonly PayPalPaymentService $paypalService,
        private readonly BACPaymentService $bacService,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger
    ) {}

    public function __invoke(ProcessPaymentCommand $command): array
    {
        $payment = $this->paymentRepository->find($command->paymentId);

        if (!$payment) {
            throw new \InvalidArgumentException('Payment not found');
        }

        $result = match ($command->gateway) {
            'stripe' => $this->stripeService->createPayment(
                $payment->getAmount(),
                $command->currency,
                ['payment_id' => $payment->getId()]
            ),
            'paypal' => $this->paypalService->createPayment(
                $payment->getAmount(),
                strtoupper($command->currency),
                [
                    'payment_id' => $payment->getId(),
                    'return_url' => $command->returnUrl ?? '',
                    'cancel_url' => $command->cancelUrl ?? '',
                ]
            ),
            'bac' => $this->bacService->createPayment(
                $payment->getAmount(),
                $command->currency === 'usd' ? 'GTQ' : $command->currency,
                [
                    'reference' => 'BAC-' . $payment->getId(),
                    'return_url' => $command->returnUrl ?? '',
                    'cancel_url' => $command->cancelUrl ?? '',
                ]
            ),
            default => throw new \InvalidArgumentException('Invalid payment gateway'),
        };

        $payment->setMetadata(array_merge($payment->getMetadata() ?? [], [
            'gateway' => $command->gateway,
            'gateway_payment_id' => $result['id'],
            'processed_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]));

        $this->entityManager->flush();

        $this->logger->info('Payment processed', [
            'payment_id' => $payment->getId(),
            'gateway' => $command->gateway,
            'gateway_payment_id' => $result['id'],
        ]);

        return [
            'payment' => $payment,
            'gateway_response' => $result,
        ];
    }
}
