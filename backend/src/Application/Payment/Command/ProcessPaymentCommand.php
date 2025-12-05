<?php

declare(strict_types=1);

namespace App\Application\Payment\Command;

/**
 * Command to process a payment through a gateway
 */
final class ProcessPaymentCommand
{
    public function __construct(
        public readonly int $paymentId,
        public readonly string $gateway = 'stripe',
        public readonly string $currency = 'usd',
        public readonly ?string $returnUrl = null,
        public readonly ?string $cancelUrl = null
    ) {}
}
