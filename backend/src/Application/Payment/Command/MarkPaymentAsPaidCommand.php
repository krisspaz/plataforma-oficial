<?php

declare(strict_types=1);

namespace App\Application\Payment\Command;

/**
 * Command to mark a payment as paid manually
 */
final class MarkPaymentAsPaidCommand
{
    public function __construct(
        public readonly int $paymentId,
        public readonly ?string $paymentMethod = null,
        public readonly ?string $receipt = null,
        public readonly ?array $metadata = null
    ) {}
}
