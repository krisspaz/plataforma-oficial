<?php

declare(strict_types=1);

namespace App\Application\Payment\Query;

/**
 * Query to get debtor report.
 */
final class GetDebtorsQuery
{
    public function __construct(
        public readonly ?int $gradeId = null,
        public readonly ?string $level = null, // warning, danger, critical
        public readonly ?int $minDaysOverdue = null
    ) {}
}
