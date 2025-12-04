<?php

declare(strict_types=1);

namespace App\Domain\Payment\Service;

use App\Domain\Payment\Entity\PaymentPlan;
use App\Domain\Payment\ValueObject\Amount;
use App\Entity\Enrollment;

/**
 * Domain service for calculating payment plans.
 */
final class PaymentPlanCalculator
{
    /**
     * Calculate monthly payment for a given total amount and number of installments.
     */
    public function calculateMonthlyPayment(Amount $totalAmount, int $numberOfInstallments): Amount
    {
        if ($numberOfInstallments < 1) {
            throw new \InvalidArgumentException('Number of installments must be at least 1');
        }

        return $totalAmount->divide($numberOfInstallments);
    }

    /**
     * Calculate total amount including any interest (if applicable).
     */
    public function calculateTotalWithInterest(Amount $baseAmount, float $interestRate, int $installments): Amount
    {
        if ($interestRate < 0) {
            throw new \InvalidArgumentException('Interest rate cannot be negative');
        }

        if ($interestRate === 0.0) {
            return $baseAmount;
        }

        $interest = $baseAmount->getValue() * ($interestRate / 100) * ($installments / 12);
        return $baseAmount->add(new Amount($interest));
    }

    /**
     * Suggest optimal payment plan based on amount.
     * 
     * @return array{installments: int, monthlyAmount: Amount, totalAmount: Amount}
     */
    public function suggestOptimalPlan(Amount $totalAmount): array
    {
        $amount = $totalAmount->getValue();

        // Business rules for Guatemala school context
        if ($amount <= 1000) {
            $installments = 1; // Cash payment
        } elseif ($amount <= 3000) {
            $installments = 3;
        } elseif ($amount <= 6000) {
            $installments = 6;
        } else {
            $installments = 10; // Academic year (10 months)
        }

        return [
            'installments' => $installments,
            'monthlyAmount' => $this->calculateMonthlyPayment($totalAmount, $installments),
            'totalAmount' => $totalAmount,
        ];
    }

    /**
     * Calculate remaining balance for a payment plan.
     */
    public function calculateRemainingBalance(PaymentPlan $plan): Amount
    {
        return $plan->getTotalPending();
    }

    /**
     * Check if payment plan is viable for given enrollment.
     */
    public function isViablePlan(Enrollment $enrollment, Amount $totalAmount, int $installments): bool
    {
        // Business rules
        $monthlyAmount = $totalAmount->divide($installments);

        // Minimum monthly payment should be at least Q50
        if ($monthlyAmount->getValue() < 50) {
            return false;
        }

        // Maximum installments for a single year
        if ($installments > 12) {
            return false;
        }

        return true;
    }

    /**
     * Generate payment schedule dates.
     *
     * @return \DateTimeImmutable[]
     */
    public function generateScheduleDates(int $numberOfInstallments, int $dayOfMonth = 5): array
    {
        $dates = [];
        $startDate = new \DateTimeImmutable('first day of next month');

        for ($i = 0; $i < $numberOfInstallments; $i++) {
            $date = $startDate->modify(sprintf('+%d months', $i));
            $maxDay = (int) $date->format('t');
            $actualDay = min($dayOfMonth, $maxDay);

            $dates[] = $date->setDate(
                (int) $date->format('Y'),
                (int) $date->format('m'),
                $actualDay
            );
        }

        return $dates;
    }
}
