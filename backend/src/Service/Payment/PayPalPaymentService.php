<?php

declare(strict_types=1);

namespace App\Service\Payment;

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\RefundRequest;
use PayPal\Api\Capture;
use Psr\Log\LoggerInterface;

final class PayPalPaymentService implements PaymentGatewayInterface
{
    private ApiContext $apiContext;

    public function __construct(
        private readonly string $clientId,
        private readonly string $clientSecret,
        private readonly string $mode, // 'sandbox' or 'live'
        private readonly LoggerInterface $logger,
    ) {
        $this->apiContext = new ApiContext(
            new OAuthTokenCredential($this->clientId, $this->clientSecret)
        );
        
        $this->apiContext->setConfig([
            'mode' => $this->mode,
            'log.LogEnabled' => true,
            'log.FileName' => '../var/log/PayPal.log',
            'log.LogLevel' => 'INFO',
        ]);
    }

    public function createPayment(float $amount, string $currency = 'USD', array $metadata = []): array
    {
        try {
            $payer = new Payer();
            $payer->setPaymentMethod('paypal');

            $amountObj = new Amount();
            $amountObj->setCurrency($currency)
                      ->setTotal($amount);

            $transaction = new Transaction();
            $transaction->setAmount($amountObj)
                       ->setDescription($metadata['description'] ?? 'School Payment')
                       ->setInvoiceNumber($metadata['invoice_number'] ?? uniqid('INV-'));

            $redirectUrls = new RedirectUrls();
            $redirectUrls->setReturnUrl($metadata['return_url'] ?? '')
                        ->setCancelUrl($metadata['cancel_url'] ?? '');

            $payment = new Payment();
            $payment->setIntent('sale')
                   ->setPayer($payer)
                   ->setRedirectUrls($redirectUrls)
                   ->setTransactions([$transaction]);

            $payment->create($this->apiContext);

            $this->logger->info('PayPal payment created', [
                'payment_id' => $payment->getId(),
                'amount' => $amount,
                'currency' => $currency,
            ]);

            return [
                'id' => $payment->getId(),
                'approval_url' => $this->getApprovalUrl($payment),
                'status' => $payment->getState(),
                'amount' => $amount,
                'currency' => $currency,
            ];
        } catch (\Exception $e) {
            $this->logger->error('PayPal payment creation failed', [
                'error' => $e->getMessage(),
                'amount' => $amount,
            ]);
            throw new \RuntimeException('Failed to create PayPal payment: ' . $e->getMessage());
        }
    }

    public function confirmPayment(string $paymentId): bool
    {
        try {
            $payment = Payment::get($paymentId, $this->apiContext);
            
            // PayPal payments are executed by the user, not confirmed programmatically
            // This method checks if payment was completed
            $success = $payment->getState() === 'approved';

            $this->logger->info('PayPal payment status checked', [
                'payment_id' => $paymentId,
                'status' => $payment->getState(),
                'success' => $success,
            ]);

            return $success;
        } catch (\Exception $e) {
            $this->logger->error('PayPal payment confirmation failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function executePayment(string $paymentId, string $payerId): bool
    {
        try {
            $payment = Payment::get($paymentId, $this->apiContext);
            
            $execution = new PaymentExecution();
            $execution->setPayerId($payerId);

            $result = $payment->execute($execution, $this->apiContext);

            $success = $result->getState() === 'approved';

            $this->logger->info('PayPal payment executed', [
                'payment_id' => $paymentId,
                'payer_id' => $payerId,
                'status' => $result->getState(),
            ]);

            return $success;
        } catch (\Exception $e) {
            $this->logger->error('PayPal payment execution failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function refund(string $paymentId, float $amount): bool
    {
        try {
            $payment = Payment::get($paymentId, $this->apiContext);
            $transactions = $payment->getTransactions();
            
            if (empty($transactions)) {
                throw new \RuntimeException('No transactions found for payment');
            }

            $relatedResources = $transactions[0]->getRelatedResources();
            if (empty($relatedResources)) {
                throw new \RuntimeException('No related resources found');
            }

            $sale = $relatedResources[0]->getSale();
            
            $refundRequest = new RefundRequest();
            $refundAmount = new Amount();
            $refundAmount->setCurrency($sale->getAmount()->getCurrency())
                        ->setTotal($amount);
            $refundRequest->setAmount($refundAmount);

            $refund = $sale->refund($refundRequest, $this->apiContext);

            $success = $refund->getState() === 'completed';

            $this->logger->info('PayPal refund processed', [
                'payment_id' => $paymentId,
                'refund_id' => $refund->getId(),
                'amount' => $amount,
                'status' => $refund->getState(),
            ]);

            return $success;
        } catch (\Exception $e) {
            $this->logger->error('PayPal refund failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function getPaymentStatus(string $paymentId): string
    {
        try {
            $payment = Payment::get($paymentId, $this->apiContext);
            return $payment->getState();
        } catch (\Exception $e) {
            $this->logger->error('Failed to retrieve PayPal payment status', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            return 'unknown';
        }
    }

    public function handleWebhook(string $payload, string $signature): array
    {
        try {
            $data = json_decode($payload, true);

            if (!$data) {
                throw new \RuntimeException('Invalid webhook payload');
            }

            $this->logger->info('PayPal webhook received', [
                'event_type' => $data['event_type'] ?? 'unknown',
                'resource_type' => $data['resource_type'] ?? 'unknown',
            ]);

            return [
                'type' => $data['event_type'] ?? 'unknown',
                'data' => $data,
                'id' => $data['id'] ?? null,
            ];
        } catch (\Exception $e) {
            $this->logger->error('PayPal webhook processing failed', [
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Webhook processing failed: ' . $e->getMessage());
        }
    }

    private function getApprovalUrl(Payment $payment): ?string
    {
        $links = $payment->getLinks();
        
        foreach ($links as $link) {
            if ($link->getRel() === 'approval_url') {
                return $link->getHref();
            }
        }
        
        return null;
    }
}
