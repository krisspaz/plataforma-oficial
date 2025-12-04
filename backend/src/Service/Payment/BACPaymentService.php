<?php

declare(strict_types=1);

namespace App\Service\Payment;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

final class BACPaymentService implements PaymentGatewayInterface
{
    private const API_BASE_URL_SANDBOX = 'https://sandbox.bac.net/api';
    private const API_BASE_URL_PRODUCTION = 'https://api.bac.net/api';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $merchantId,
        private readonly string $apiKey,
        private readonly string $apiSecret,
        private readonly string $mode, // 'sandbox' or 'production'
        private readonly LoggerInterface $logger,
    ) {}

    public function createPayment(float $amount, string $currency = 'GTQ', array $metadata = []): array
    {
        try {
            $baseUrl = $this->mode === 'production' 
                ? self::API_BASE_URL_PRODUCTION 
                : self::API_BASE_URL_SANDBOX;

            $response = $this->httpClient->request('POST', $baseUrl . '/payments', [
                'headers' => $this->getAuthHeaders(),
                'json' => [
                    'merchant_id' => $this->merchantId,
                    'amount' => $amount,
                    'currency' => $currency,
                    'description' => $metadata['description'] ?? 'School Payment',
                    'reference' => $metadata['reference'] ?? uniqid('BAC-'),
                    'return_url' => $metadata['return_url'] ?? '',
                    'cancel_url' => $metadata['cancel_url'] ?? '',
                    'webhook_url' => $metadata['webhook_url'] ?? '',
                ],
            ]);

            $data = $response->toArray();

            $this->logger->info('BAC payment created', [
                'transaction_id' => $data['transaction_id'] ?? null,
                'amount' => $amount,
                'currency' => $currency,
            ]);

            return [
                'id' => $data['transaction_id'] ?? null,
                'payment_url' => $data['payment_url'] ?? null,
                'status' => $data['status'] ?? 'pending',
                'amount' => $amount,
                'currency' => $currency,
            ];
        } catch (\Exception $e) {
            $this->logger->error('BAC payment creation failed', [
                'error' => $e->getMessage(),
                'amount' => $amount,
            ]);
            throw new \RuntimeException('Failed to create BAC payment: ' . $e->getMessage());
        }
    }

    public function confirmPayment(string $paymentId): bool
    {
        try {
            $baseUrl = $this->mode === 'production' 
                ? self::API_BASE_URL_PRODUCTION 
                : self::API_BASE_URL_SANDBOX;

            $response = $this->httpClient->request('GET', $baseUrl . '/payments/' . $paymentId, [
                'headers' => $this->getAuthHeaders(),
            ]);

            $data = $response->toArray();
            $success = isset($data['status']) && in_array($data['status'], ['approved', 'completed']);

            $this->logger->info('BAC payment confirmation', [
                'transaction_id' => $paymentId,
                'status' => $data['status'] ?? 'unknown',
                'success' => $success,
            ]);

            return $success;
        } catch (\Exception $e) {
            $this->logger->error('BAC payment confirmation failed', [
                'transaction_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function refund(string $paymentId, float $amount): bool
    {
        try {
            $baseUrl = $this->mode === 'production' 
                ? self::API_BASE_URL_PRODUCTION 
                : self::API_BASE_URL_SANDBOX;

            $response = $this->httpClient->request('POST', $baseUrl . '/payments/' . $paymentId . '/refund', [
                'headers' => $this->getAuthHeaders(),
                'json' => [
                    'amount' => $amount,
                    'reason' => 'Customer request',
                ],
            ]);

            $data = $response->toArray();
            $success = isset($data['status']) && $data['status'] === 'refunded';

            $this->logger->info('BAC refund processed', [
                'transaction_id' => $paymentId,
                'refund_id' => $data['refund_id'] ?? null,
                'amount' => $amount,
                'status' => $data['status'] ?? 'unknown',
            ]);

            return $success;
        } catch (\Exception $e) {
            $this->logger->error('BAC refund failed', [
                'transaction_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function getPaymentStatus(string $paymentId): string
    {
        try {
            $baseUrl = $this->mode === 'production' 
                ? self::API_BASE_URL_PRODUCTION 
                : self::API_BASE_URL_SANDBOX;

            $response = $this->httpClient->request('GET', $baseUrl . '/payments/' . $paymentId, [
                'headers' => $this->getAuthHeaders(),
            ]);

            $data = $response->toArray();
            return $data['status'] ?? 'unknown';
        } catch (\Exception $e) {
            $this->logger->error('Failed to retrieve BAC payment status', [
                'transaction_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            return 'unknown';
        }
    }

    public function handleWebhook(string $payload, string $signature): array
    {
        try {
            // Verify webhook signature
            $expectedSignature = hash_hmac('sha256', $payload, $this->apiSecret);
            
            if (!hash_equals($expectedSignature, $signature)) {
                throw new \RuntimeException('Invalid webhook signature');
            }

            $data = json_decode($payload, true);

            if (!$data) {
                throw new \RuntimeException('Invalid webhook payload');
            }

            $this->logger->info('BAC webhook received', [
                'event_type' => $data['event_type'] ?? 'unknown',
                'transaction_id' => $data['transaction_id'] ?? null,
            ]);

            return [
                'type' => $data['event_type'] ?? 'unknown',
                'data' => $data,
                'id' => $data['transaction_id'] ?? null,
            ];
        } catch (\Exception $e) {
            $this->logger->error('BAC webhook processing failed', [
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Webhook processing failed: ' . $e->getMessage());
        }
    }

    private function getAuthHeaders(): array
    {
        $timestamp = time();
        $signature = hash_hmac('sha256', $this->merchantId . $timestamp, $this->apiSecret);

        return [
            'X-Merchant-ID' => $this->merchantId,
            'X-API-Key' => $this->apiKey,
            'X-Timestamp' => (string) $timestamp,
            'X-Signature' => $signature,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }
}
