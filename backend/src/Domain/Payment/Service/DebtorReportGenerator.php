<?php

declare(strict_types=1);

namespace App\Domain\Payment\Service;

use App\Domain\Payment\Entity\Installment;
use App\Domain\Payment\Repository\InstallmentRepositoryInterface;
use App\Domain\Payment\ValueObject\Amount;
use DateTimeImmutable;

/**
 * Domain service for generating debtor reports.
 */
final class DebtorReportGenerator
{
    public function __construct(
        private readonly InstallmentRepositoryInterface $installmentRepository
    ) {}

    /**
     * Generate a comprehensive debtor report.
     *
     * @return array{
     *     summary: array{total_debtors: int, total_amount: float, critical_count: int},
     *     debtors: array<int, array{
     *         student_id: int,
     *         student_name: string,
     *         grade: string,
     *         parent_name: string,
     *         parent_phone: string,
     *         total_overdue: float,
     *         days_overdue: int,
     *         level: string,
     *         installments: array
     *     }>
     * }
     */
    public function generate(): array
    {
        $overdueInstallments = $this->installmentRepository->findOverdue();

        $debtorsByStudent = $this->groupByStudent($overdueInstallments);
        $debtors = $this->formatDebtors($debtorsByStudent);

        return [
            'summary' => $this->calculateSummary($debtors),
            'debtors' => $debtors,
            'generated_at' => (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Generate daily closure report.
     *
     * @return array{
     *     date: string,
     *     total_collected: float,
     *     payment_count: int,
     *     by_method: array,
     *     payments: array
     * }
     */
    public function generateDailyClosure(DateTimeImmutable $date): array
    {
        $paidInstallments = $this->installmentRepository->findPaidOnDate($date);

        $byMethod = [];
        $totalCollected = Amount::zero();
        $payments = [];

        foreach ($paidInstallments as $installment) {
            $method = $installment->getPaymentMethod() ?? 'unknown';
            $amount = $installment->getAmount();

            if (!isset($byMethod[$method])) {
                $byMethod[$method] = [
                    'count' => 0,
                    'total' => 0.0,
                ];
            }

            $byMethod[$method]['count']++;
            $byMethod[$method]['total'] += $amount->getValue();
            $totalCollected = $totalCollected->add($amount);

            $payments[] = [
                'student_name' => $this->getStudentNameFromInstallment($installment),
                'amount' => $amount->getValue(),
                'method' => $method,
                'receipt' => $installment->getReceiptNumber(),
                'paid_at' => $installment->getPaidAt()?->format('H:i:s'),
                'installment' => $installment->getFormattedNumber(),
            ];
        }

        return [
            'date' => $date->format('Y-m-d'),
            'total_collected' => $totalCollected->getValue(),
            'payment_count' => count($paidInstallments),
            'by_method' => $byMethod,
            'payments' => $payments,
        ];
    }

    /**
     * @param Installment[] $installments
     * @return array<int, Installment[]>
     */
    private function groupByStudent(array $installments): array
    {
        $grouped = [];

        foreach ($installments as $installment) {
            $studentId = $installment->getPaymentPlan()->getEnrollment()->getStudent()->getId();

            if (!isset($grouped[$studentId])) {
                $grouped[$studentId] = [];
            }

            $grouped[$studentId][] = $installment;
        }

        return $grouped;
    }

    /**
     * @param array<int, Installment[]> $debtorsByStudent
     * @return array
     */
    private function formatDebtors(array $debtorsByStudent): array
    {
        $debtors = [];

        foreach ($debtorsByStudent as $studentId => $installments) {
            if (empty($installments)) {
                continue;
            }

            $firstInstallment = $installments[0];
            $enrollment = $firstInstallment->getPaymentPlan()->getEnrollment();
            $student = $enrollment->getStudent();

            $totalOverdue = Amount::zero();
            $maxDaysOverdue = 0;
            $installmentDetails = [];

            foreach ($installments as $installment) {
                $totalOverdue = $totalOverdue->add($installment->getAmount());
                $daysOverdue = $installment->getDaysOverdue();

                if ($daysOverdue > $maxDaysOverdue) {
                    $maxDaysOverdue = $daysOverdue;
                }

                $installmentDetails[] = [
                    'number' => $installment->getFormattedNumber(),
                    'amount' => $installment->getAmount()->getValue(),
                    'due_date' => $installment->getDueDate()->format(),
                    'days_overdue' => $daysOverdue,
                    'level' => $installment->getOverdueLevel(),
                ];
            }

            $debtors[] = [
                'student_id' => $studentId,
                'student_name' => sprintf('%s %s', $student->getFirstName(), $student->getLastName()),
                'grade' => $enrollment->getGrade()->getName(),
                'section' => $enrollment->getSection()->getName(),
                'total_overdue' => $totalOverdue->getValue(),
                'days_overdue' => $maxDaysOverdue,
                'level' => $this->determineLevel($maxDaysOverdue),
                'installments' => $installmentDetails,
                'installments_count' => count($installments),
            ];
        }

        // Sort by days overdue (most urgent first)
        usort($debtors, fn($a, $b) => $b['days_overdue'] <=> $a['days_overdue']);

        return $debtors;
    }

    private function calculateSummary(array $debtors): array
    {
        $totalAmount = 0.0;
        $criticalCount = 0;

        foreach ($debtors as $debtor) {
            $totalAmount += $debtor['total_overdue'];

            if ($debtor['level'] === 'critical') {
                $criticalCount++;
            }
        }

        return [
            'total_debtors' => count($debtors),
            'total_amount' => $totalAmount,
            'critical_count' => $criticalCount,
        ];
    }

    private function determineLevel(int $daysOverdue): string
    {
        if ($daysOverdue <= 15) {
            return 'warning';
        }

        if ($daysOverdue <= 30) {
            return 'danger';
        }

        return 'critical';
    }

    private function getStudentNameFromInstallment(Installment $installment): string
    {
        $student = $installment->getPaymentPlan()->getEnrollment()->getStudent();
        return sprintf('%s %s', $student->getFirstName(), $student->getLastName());
    }
}
