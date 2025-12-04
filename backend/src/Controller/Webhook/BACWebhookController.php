<?php

declare(strict_types=1);

namespace App\Controller\Webhook;

use App\Service\Payment\BACPaymentService;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/webhooks')]
class BACWebhookController extends AbstractController
{
    public function __construct(
        private readonly BACPaymentService $bacService,
        private readonly PaymentRepository $paymentRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
    ) {}

    #[Route('/bac', name: 'webhook_bac', methods: ['POST'])]
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->headers->get('X-BAC-Signature', '');

        try {
            $event = $this->bacService->handleWebhook($payload, $signature);

            $this->logger->info('Processing BAC webhook', [
                'event_type' => $event['type'],
                'transaction_id' => $event['id'],
            ]);

            // Handle different event types
            match ($event['type']) {
                'payment.approved' => $this->handlePaymentApproved($event['data']),
                'payment.rejected' => $this->handlePaymentRejected($event['data']),
                'payment.refunded' => $this->handleRefund($event['data']),
                'payment.expired' => $this->handlePaymentExpired($event['data']),
                default => $this->logger->info('Unhandled BAC event type', ['type' => $event['type']]),
            };

            return $this->json(['received' => true], Response::HTTP_OK);

        } catch (\RuntimeException $e) {
            $this->logger->error('BAC webhook verification failed', [
                'error' => $e->getMessage(),
            ]);
            return $this->json(['error' => 'Webhook verification failed'], Response::HTTP_BAD_REQUEST);

        } catch (\Exception $e) {
            $this->logger->error('BAC webhook processing error', [
                'error' => $e->getMessage(),
            ]);
            return $this->json(['error' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function handlePaymentApproved(array $data): void
    {
        $transactionId = $data['transaction_id'] ?? null;
        $reference = $data['reference'] ?? null;

        if (!$reference) {
            $this->logger->warning('Payment approved but no reference found');
            return;
        }

        // Extract payment ID from reference (assuming format: BAC-{payment_id})
        $paymentId = (int) str_replace('BAC-', '', $reference);

        $payment = $this->paymentRepository->find($paymentId);

        if (!$payment) {
            $this->logger->error('Payment not found', ['payment_id' => $paymentId]);
            return;
        }

        $payment->setStatus('paid');
        $payment->setPaidDate(new \DateTime());
        $payment->setPaymentMethod('bac');
        $payment->setMetadata(array_merge($payment->getMetadata() ?? [], [
            'bac_transaction_id' => $transactionId,
            'bac_authorization_code' => $data['authorization_code'] ?? null,
            'bac_event_processed_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]));

        $this->entityManager->flush();

        $this->logger->info('BAC payment marked as paid', [
            'payment_id' => $paymentId,
            'bac_transaction_id' => $transactionId,
        ]);
    }

    private function handlePaymentRejected(array $data): void
    {
        $transactionId = $data['transaction_id'] ?? null;
        $reference = $data['reference'] ?? null;

        if (!$reference) {
            return;
        }

        $paymentId = (int) str_replace('BAC-', '', $reference);
        $payment = $this->paymentRepository->find($paymentId);

        if (!$payment) {
            return;
        }

        $payment->setStatus('failed');
        $payment->setMetadata(array_merge($payment->getMetadata() ?? [], [
            'bac_transaction_id' => $transactionId,
            'rejection_reason' => $data['reason'] ?? 'Unknown',
            'rejected_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]));

        $this->entityManager->flush();

        $this->logger->warning('BAC payment rejected', [
            'payment_id' => $paymentId,
            'bac_transaction_id' => $transactionId,
        ]);
    }

    private function handleRefund(array $data): void
    {
        $transactionId = $data['transaction_id'] ?? null;
        $refundAmount = $data['refund_amount'] ?? 0;

        $this->logger->info('BAC refund processed', [
            'transaction_id' => $transactionId,
            'refund_amount' => $refundAmount,
        ]);
    }

    private function handlePaymentExpired(array $data): void
    {
        $transactionId = $data['transaction_id'] ?? null;
        $reference = $data['reference'] ?? null;

        if (!$reference) {
            return;
        }

        $paymentId = (int) str_replace('BAC-', '', $reference);
        $payment = $this->paymentRepository->find($paymentId);

        if (!$payment) {
            return;
        }

        $payment->setStatus('expired');
        $payment->setMetadata(array_merge($payment->getMetadata() ?? [], [
            'bac_transaction_id' => $transactionId,
            'expired_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]));

        $this->entityManager->flush();

        $this->logger->info('BAC payment expired', [
            'payment_id' => $paymentId,
            'bac_transaction_id' => $transactionId,
        ]);
    }
}
