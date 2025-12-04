<?php

declare(strict_types=1);

namespace App\Application\Payment\Command;

use Symfony\Component\Uid\Uuid;

/**
 * Command to record a payment for an installment.
 */
final class RecordInstallmentPaymentCommand
{
    public function __construct(
        public readonly string $installmentId,
        public readonly string $paymentMethod,
        public readonly ?string $receiptNumber = null,
        public readonly ?int $recordedByUserId = null,
        public readonly ?array $metadata = null
    ) {}

    public function getInstallmentUuid(): Uuid
    {
        return Uuid::fromString($this->installmentId);
    }
}
