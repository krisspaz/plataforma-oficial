<?php

declare(strict_types=1);

namespace App\Application\Payment\Command;

use App\Domain\Payment\ValueObject\Amount;

/**
 * Command to create a new payment plan.
 */
final class CreatePaymentPlanCommand
{
    public function __construct(
        public readonly int $enrollmentId,
        public readonly float $totalAmount,
        public readonly int $numberOfInstallments,
        public readonly int $dayOfMonth = 5,
        public readonly ?string $currency = 'GTQ',
        public readonly ?array $metadata = null
    ) {}

    public function getTotalAmountAsValueObject(): Amount
    {
        return new Amount($this->totalAmount, $this->currency ?? 'GTQ');
    }
}
