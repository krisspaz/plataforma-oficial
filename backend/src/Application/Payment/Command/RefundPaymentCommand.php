<?php

declare(strict_types=1);

namespace App\Application\Payment\Command;

/**
 * Command to refund a payment
 */
final class RefundPaymentCommand
{
    public function __construct(
        public readonly int $paymentId,
        public readonly ?float $amount = null
    ) {}
}
