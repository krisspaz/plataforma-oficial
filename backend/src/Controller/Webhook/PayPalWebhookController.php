<?php

declare(strict_types=1);

namespace App\Controller\Webhook;

use App\Service\Payment\PayPalPaymentService;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/webhooks')]
class PayPalWebhookController extends AbstractController
{
    public function __construct(
        private readonly PayPalPaymentService $paypalService,
        private readonly PaymentRepository $paymentRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {}

    #[Route('/paypal', name: 'webhook_paypal', methods: ['POST'])]
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->headers->get('PAYPAL-TRANSMISSION-SIG', '');

        try {
            $event = $this->paypalService->handleWebhook($payload, $signature);

            $this->logger->info('Processing PayPal webhook', [
                'event_type' => $event['type'],
                'resource_type' => $event['data']['resource_type'] ?? 'unknown',
            ]);

            // Handle different event types
            match ($event['type']) {
                'PAYMENT.SALE.COMPLETED' => $this->handlePaymentCompleted($event['data']),
                'PAYMENT.SALE.DENIED' => $this->handlePaymentDenied($event['data']),
                'PAYMENT.SALE.REFUNDED' => $this->handleRefund($event['data']),
                'PAYMENT.SALE.REVERSED' => $this->handleReversal($event['data']),
                default => $this->logger->info('Unhandled PayPal event type', ['type' => $event['type']]),
            };

            return $this->json(['received' => true], Response::HTTP_OK);

        } catch (\Exception $e) {
            $this->logger->error('PayPal webhook processing error', [
                'error' => $e->getMessage(),
            ]);
            return $this->json(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function handlePaymentCompleted(array $data): void
    {
        $resource = $data['resource'] ?? [];
        $paymentId = $resource['custom'] ?? null; // Assuming we store our payment ID in custom field

        if (!$paymentId) {
            $this->logger->warning('Payment completed but no payment_id found');
            return;
        }

        $payment = $this->paymentRepository->find($paymentId);

        if (!$payment) {
            $this->logger->error('Payment not found', ['payment_id' => $paymentId]);
            return;
        }

        $payment->setStatus('paid');
        $payment->setPaidDate(new \DateTime());
        $payment->setPaymentMethod('paypal');
        $payment->setMetadata(array_merge($payment->getMetadata() ?? [], [
            'paypal_sale_id' => $resource['id'] ?? null,
            'paypal_transaction_id' => $resource['parent_payment'] ?? null,
            'paypal_event_processed_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]));

        $this->entityManager->flush();

        $this->logger->info('PayPal payment marked as paid', [
            'payment_id' => $paymentId,
            'paypal_sale_id' => $resource['id'] ?? null,
        ]);
    }

    private function handlePaymentDenied(array $data): void
    {
        $resource = $data['resource'] ?? [];
        $paymentId = $resource['custom'] ?? null;

        if (!$paymentId) {
            return;
        }

        $payment = $this->paymentRepository->find($paymentId);

        if (!$payment) {
            return;
        }

        $payment->setStatus('failed');
        $payment->setMetadata(array_merge($payment->getMetadata() ?? [], [
            'paypal_sale_id' => $resource['id'] ?? null,
            'denial_reason' => $resource['reason_code'] ?? 'Unknown',
            'denied_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]));

        $this->entityManager->flush();

        $this->logger->warning('PayPal payment denied', [
            'payment_id' => $paymentId,
            'paypal_sale_id' => $resource['id'] ?? null,
        ]);
    }

    private function handleRefund(array $data): void
    {
        $resource = $data['resource'] ?? [];
        $saleId = $resource['sale_id'] ?? null;

        $this->logger->info('PayPal refund processed', [
            'sale_id' => $saleId,
            'refund_id' => $resource['id'] ?? null,
        ]);
    }

    private function handleReversal(array $data): void
    {
        $resource = $data['resource'] ?? [];
        
        $this->logger->warning('PayPal payment reversed', [
            'sale_id' => $resource['id'] ?? null,
        ]);
    }
}
