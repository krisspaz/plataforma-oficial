<?php

declare(strict_types=1);

namespace App\Application\Payment\Command;

/**
 * Command to create a new payment
 */
final class CreatePaymentCommand
{
    public function __construct(
        public readonly int $enrollmentId,
        public readonly float $amount,
        public readonly string $paymentType = 'contado',
        public readonly ?string $paymentMethod = null,
        public readonly ?string $dueDate = null,
        public readonly ?array $metadata = null
    ) {}
}
