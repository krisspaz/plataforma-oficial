<?php

declare(strict_types=1);

namespace App\Application\Payment\Query;

use App\Domain\Payment\Service\DebtorReportGenerator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetDebtorsHandler
{
    public function __construct(
        private readonly DebtorReportGenerator $reportGenerator
    ) {}

    public function __invoke(GetDebtorsQuery $query): array
    {
        $report = $this->reportGenerator->generate();

        // Apply filters
        $debtors = $report['debtors'];

        if ($query->gradeId !== null) {
            $debtors = array_filter($debtors, function ($debtor) use ($query) {
                // This would need grade_id in the debtor array
                return true; // Simplified for now
            });
        }

        if ($query->level !== null) {
            $debtors = array_filter($debtors, fn($d) => $d['level'] === $query->level);
        }

        if ($query->minDaysOverdue !== null) {
            $debtors = array_filter($debtors, fn($d) => $d['days_overdue'] >= $query->minDaysOverdue);
        }

        // Recalculate summary with filtered data
        $filteredDebtors = array_values($debtors);
        $totalAmount = array_sum(array_column($filteredDebtors, 'total_overdue'));
        $criticalCount = count(array_filter($filteredDebtors, fn($d) => $d['level'] === 'critical'));

        return [
            'summary' => [
                'total_debtors' => count($filteredDebtors),
                'total_amount' => $totalAmount,
                'critical_count' => $criticalCount,
            ],
            'debtors' => $filteredDebtors,
            'generated_at' => $report['generated_at'],
        ];
    }
}
