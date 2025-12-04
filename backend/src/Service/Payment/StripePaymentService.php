<?php

declare(strict_types=1);

namespace App\Service\Payment;

use Stripe\StripeClient;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Psr\Log\LoggerInterface;

final class StripePaymentService implements PaymentGatewayInterface
{
    private StripeClient $stripe;

    public function __construct(
        private readonly string $secretKey,
        private readonly string $webhookSecret,
        private readonly LoggerInterface $logger,
    ) {
        $this->stripe = new StripeClient($this->secretKey);
    }

    public function createPayment(float $amount, string $currency = 'usd', array $metadata = []): array
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => (int) ($amount * 100), // Stripe uses cents
                'currency' => $currency,
                'metadata' => $metadata,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            $this->logger->info('Stripe payment intent created', [
                'payment_intent_id' => $paymentIntent->id,
                'amount' => $amount,
                'currency' => $currency,
            ]);

            return [
                'id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret,
                'status' => $paymentIntent->status,
                'amount' => $amount,
                'currency' => $currency,
            ];
        } catch (\Exception $e) {
            $this->logger->error('Stripe payment creation failed', [
                'error' => $e->getMessage(),
                'amount' => $amount,
            ]);
            throw new \RuntimeException('Failed to create Stripe payment: ' . $e->getMessage());
        }
    }

    public function confirmPayment(string $paymentId): bool
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentId);
            
            if ($paymentIntent->status === 'requires_confirmation') {
                $paymentIntent = $this->stripe->paymentIntents->confirm($paymentId);
            }

            $success = in_array($paymentIntent->status, ['succeeded', 'processing']);

            $this->logger->info('Stripe payment confirmation', [
                'payment_intent_id' => $paymentId,
                'status' => $paymentIntent->status,
                'success' => $success,
            ]);

            return $success;
        } catch (\Exception $e) {
            $this->logger->error('Stripe payment confirmation failed', [
                'payment_intent_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function refund(string $paymentId, float $amount): bool
    {
        try {
            $refund = $this->stripe->refunds->create([
                'payment_intent' => $paymentId,
                'amount' => (int) ($amount * 100),
            ]);

            $success = $refund->status === 'succeeded';

            $this->logger->info('Stripe refund processed', [
                'payment_intent_id' => $paymentId,
                'refund_id' => $refund->id,
                'amount' => $amount,
                'status' => $refund->status,
            ]);

            return $success;
        } catch (\Exception $e) {
            $this->logger->error('Stripe refund failed', [
                'payment_intent_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function getPaymentStatus(string $paymentId): string
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentId);
            return $paymentIntent->status;
        } catch (\Exception $e) {
            $this->logger->error('Failed to retrieve Stripe payment status', [
                'payment_intent_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);
            return 'unknown';
        }
    }

    public function handleWebhook(string $payload, string $signature): array
    {
        try {
            $event = Webhook::constructEvent(
                $payload,
                $signature,
                $this->webhookSecret
            );

            $this->logger->info('Stripe webhook received', [
                'event_type' => $event->type,
                'event_id' => $event->id,
            ]);

            return [
                'type' => $event->type,
                'data' => $event->data->toArray(),
                'id' => $event->id,
            ];
        } catch (SignatureVerificationException $e) {
            $this->logger->error('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Invalid webhook signature');
        } catch (\Exception $e) {
            $this->logger->error('Stripe webhook processing failed', [
                'error' => $e->getMessage(),
            ]);
            throw new \RuntimeException('Webhook processing failed: ' . $e->getMessage());
        }
    }
}
