<?php

declare(strict_types=1);

namespace App\Controller\Webhook;

use App\Service\Payment\StripePaymentService;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/webhooks')]
class StripeWebhookController extends AbstractController
{
    public function __construct(
        private readonly StripePaymentService $stripeService,
        private readonly PaymentRepository $paymentRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {}

    #[Route('/stripe', name: 'webhook_stripe', methods: ['POST'])]
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->headers->get('Stripe-Signature');

        if (!$signature) {
            $this->logger->warning('Stripe webhook received without signature');
            return $this->json(['error' => 'No signature provided'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $event = $this->stripeService->handleWebhook($payload, $signature);

            $this->logger->info('Processing Stripe webhook', [
                'event_type' => $event['type'],
                'event_id' => $event['id'],
            ]);

            // Handle different event types
            match ($event['type']) {
                'payment_intent.succeeded' => $this->handlePaymentSucceeded($event['data']),
                'payment_intent.payment_failed' => $this->handlePaymentFailed($event['data']),
                'charge.refunded' => $this->handleRefund($event['data']),
                'payment_intent.canceled' => $this->handlePaymentCanceled($event['data']),
                default => $this->logger->info('Unhandled Stripe event type', ['type' => $event['type']]),
            };

            return $this->json(['received' => true], Response::HTTP_OK);

        } catch (\RuntimeException $e) {
            $this->logger->error('Stripe webhook verification failed', [
                'error' => $e->getMessage(),
            ]);
            return $this->json(['error' => 'Webhook verification failed'], Response::HTTP_BAD_REQUEST);

        } catch (\Exception $e) {
            $this->logger->error('Stripe webhook processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->json(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function handlePaymentSucceeded(array $data): void
    {
        $paymentIntentId = $data['object']['id'] ?? null;
        $metadata = $data['object']['metadata'] ?? [];
        $paymentId = $metadata['payment_id'] ?? null;

        if (!$paymentId) {
            $this->logger->warning('Payment succeeded but no payment_id in metadata', [
                'payment_intent_id' => $paymentIntentId,
            ]);
            return;
        }

        $payment = $this->paymentRepository->find($paymentId);

        if (!$payment) {
            $this->logger->error('Payment not found', ['payment_id' => $paymentId]);
            return;
        }

        $payment->setStatus('paid');
        $payment->setPaidDate(new \DateTime());
        $payment->setPaymentMethod('stripe');
        $payment->setMetadata(array_merge($payment->getMetadata() ?? [], [
            'stripe_payment_intent_id' => $paymentIntentId,
            'stripe_event_processed_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]));

        $this->entityManager->flush();

        $this->logger->info('Payment marked as paid', [
            'payment_id' => $paymentId,
            'stripe_payment_intent_id' => $paymentIntentId,
        ]);
    }

    private function handlePaymentFailed(array $data): void
    {
        $paymentIntentId = $data['object']['id'] ?? null;
        $metadata = $data['object']['metadata'] ?? [];
        $paymentId = $metadata['payment_id'] ?? null;

        if (!$paymentId) {
            return;
        }

        $payment = $this->paymentRepository->find($paymentId);

        if (!$payment) {
            return;
        }

        $payment->setStatus('failed');
        $payment->setMetadata(array_merge($payment->getMetadata() ?? [], [
            'stripe_payment_intent_id' => $paymentIntentId,
            'failure_reason' => $data['object']['last_payment_error']['message'] ?? 'Unknown',
            'failed_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]));

        $this->entityManager->flush();

        $this->logger->warning('Payment failed', [
            'payment_id' => $paymentId,
            'stripe_payment_intent_id' => $paymentIntentId,
        ]);
    }

    private function handleRefund(array $data): void
    {
        $chargeId = $data['object']['id'] ?? null;
        $refundAmount = ($data['object']['amount_refunded'] ?? 0) / 100; // Convert from cents

        $this->logger->info('Refund processed', [
            'charge_id' => $chargeId,
            'refund_amount' => $refundAmount,
        ]);

        // Find payment by charge ID and update status
        // This would require storing the charge ID in payment metadata
    }

    private function handlePaymentCanceled(array $data): void
    {
        $paymentIntentId = $data['object']['id'] ?? null;
        $metadata = $data['object']['metadata'] ?? [];
        $paymentId = $metadata['payment_id'] ?? null;

        if (!$paymentId) {
            return;
        }

        $payment = $this->paymentRepository->find($paymentId);

        if (!$payment) {
            return;
        }

        $payment->setStatus('cancelled');
        $payment->setMetadata(array_merge($payment->getMetadata() ?? [], [
            'stripe_payment_intent_id' => $paymentIntentId,
            'canceled_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]));

        $this->entityManager->flush();

        $this->logger->info('Payment canceled', [
            'payment_id' => $paymentId,
            'stripe_payment_intent_id' => $paymentIntentId,
        ]);
    }
}
