<?php

declare(strict_types=1);

namespace App\Service\Payment;

interface PaymentGatewayInterface
{
    /**
     * Create a payment intent/order
     */
    public function createPayment(float $amount, string $currency, array $metadata = []): array;

    /**
     * Confirm/capture a payment
     */
    public function confirmPayment(string $paymentId): bool;

    /**
     * Refund a payment
     */
    public function refund(string $paymentId, float $amount): bool;

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $paymentId): string;

    /**
     * Handle webhook notification
     */
    public function handleWebhook(string $payload, string $signature): array;
}
