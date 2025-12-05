<?php

declare(strict_types=1);

namespace App\Application\Contract\Command;

final class CreateContractCommand
{
    public function __construct(
        public readonly int $enrollmentId,
        public readonly int $parentId,
        public readonly float $totalAmount,
        public readonly ?int $installments = null
    ) {}
}
