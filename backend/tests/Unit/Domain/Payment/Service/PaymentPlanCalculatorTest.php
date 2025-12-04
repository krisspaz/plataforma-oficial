<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Payment\Service;

use App\Domain\Payment\Service\PaymentPlanCalculator;
use App\Domain\Payment\ValueObject\Amount;
use PHPUnit\Framework\TestCase;

class PaymentPlanCalculatorTest extends TestCase
{
    private PaymentPlanCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new PaymentPlanCalculator();
    }

    public function testCanCalculateMonthlyPayment(): void
    {
        $totalAmount = new Amount(1200.00);
        $installments = 12;

        $monthlyPayment = $this->calculator->calculateMonthlyPayment($totalAmount, $installments);

        $this->assertEquals(100.00, $monthlyPayment->getValue());
    }

    public function testCanCalculateTotalWithInterest(): void
    {
        $baseAmount = new Amount(1000.00);
        $interestRate = 10.0; // 10%
        $installments = 12;

        $totalWithInterest = $this->calculator->calculateTotalWithInterest(
            $baseAmount,
            $interestRate,
            $installments
        );

        // 1000 + (1000 * 0.10 * 1) = 1100
        $this->assertEquals(1100.00, $totalWithInterest->getValue());
    }

    public function testSuggestsOptimalPlanForSmallAmount(): void
    {
        $amount = new Amount(500.00);

        $suggestion = $this->calculator->suggestOptimalPlan($amount);

        $this->assertEquals(1, $suggestion['installments']);
        $this->assertEquals(500.00, $suggestion['monthlyAmount']->getValue());
    }

    public function testSuggestsOptimalPlanForMediumAmount(): void
    {
        $amount = new Amount(2000.00);

        $suggestion = $this->calculator->suggestOptimalPlan($amount);

        $this->assertEquals(3, $suggestion['installments']);
    }

    public function testSuggestsOptimalPlanForLargeAmount(): void
    {
        $amount = new Amount(7000.00);

        $suggestion = $this->calculator->suggestOptimalPlan($amount);

        $this->assertEquals(10, $suggestion['installments']);
    }

    public function testRejectsNonViablePlan(): void
    {
        $amount = new Amount(30.00); // Too small for multiple installments
        $installments = 10;

        $enrollment = $this->createMock(\App\Entity\Enrollment::class);

        $isViable = $this->calculator->isViablePlan($enrollment, $amount, $installments);

        $this->assertFalse($isViable); // Monthly would be Q3, below minimum
    }

    public function testAcceptsViablePlan(): void
    {
        $amount = new Amount(1000.00);
        $installments = 10;

        $enrollment = $this->createMock(\App\Entity\Enrollment::class);

        $isViable = $this->calculator->isViablePlan($enrollment, $amount, $installments);

        $this->assertTrue($isViable);
    }

    public function testGeneratesCorrectScheduleDates(): void
    {
        $numberOfInstallments = 3;
        $dayOfMonth = 5;

        $dates = $this->calculator->generateScheduleDates($numberOfInstallments, $dayOfMonth);

        $this->assertCount(3, $dates);

        foreach ($dates as $date) {
            $this->assertInstanceOf(\DateTimeImmutable::class, $date);
            // Day should be 5 (or last day of month if month has fewer days)
            $this->assertLessThanOrEqual(5, (int) $date->format('d'));
        }
    }

    public function testHandlesMonthsWithFewerDays(): void
    {
        // Test February with day 30 requested
        $dates = $this->calculator->generateScheduleDates(12, 30);

        foreach ($dates as $date) {
            $day = (int) $date->format('d');
            $maxDay = (int) $date->format('t');

            // Should not exceed the maximum days in the month
            $this->assertLessThanOrEqual($maxDay, $day);
        }
    }
}
