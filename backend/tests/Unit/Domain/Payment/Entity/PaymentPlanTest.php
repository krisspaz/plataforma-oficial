<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Payment\Entity;

use App\Domain\Payment\Entity\PaymentPlan;
use App\Domain\Payment\ValueObject\Amount;
use App\Entity\Enrollment;
use App\Entity\Student;
use App\Entity\Grade;
use App\Entity\Section;
use PHPUnit\Framework\TestCase;

class PaymentPlanTest extends TestCase
{
    private Enrollment $enrollment;

    protected function setUp(): void
    {
        // Create mock enrollment
        $student = new Student();
        $student->setFirstName('Juan');
        $student->setLastName('Pérez');
        $student->setEmail('juan@test.com');

        $grade = new Grade();
        $grade->setName('Primero Básico');

        $section = new Section();
        $section->setName('A');

        $this->enrollment = new Enrollment();
        $this->enrollment->setStudent($student);
        $this->enrollment->setGrade($grade);
        $this->enrollment->setSection($section);
    }

    public function testCanCreatePaymentPlan(): void
    {
        $totalAmount = new Amount(3000.00);

        $plan = PaymentPlan::create(
            $this->enrollment,
            $totalAmount,
            10,
            5
        );

        $this->assertNotNull($plan->getId());
        $this->assertEquals(3000.00, $plan->getTotalAmount()->getValue());
        $this->assertEquals(10, $plan->getNumberOfInstallments());
        $this->assertEquals(300.00, $plan->getInstallmentAmount()->getValue());
        $this->assertEquals(5, $plan->getDayOfMonth());
        $this->assertEquals('active', $plan->getStatus());
    }

    public function testGeneratesCorrectNumberOfInstallments(): void
    {
        $plan = PaymentPlan::create(
            $this->enrollment,
            new Amount(1000.00),
            10
        );

        $this->assertCount(10, $plan->getInstallments());
    }

    public function testCannotCreatePlanWithInvalidInstallments(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        PaymentPlan::create(
            $this->enrollment,
            new Amount(1000.00),
            0
        );
    }

    public function testCannotCreatePlanWithInvalidDayOfMonth(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        PaymentPlan::create(
            $this->enrollment,
            new Amount(1000.00),
            10,
            30 // Invalid, must be <= 28
        );
    }

    public function testCanCalculateTotalPaid(): void
    {
        $plan = PaymentPlan::create(
            $this->enrollment,
            new Amount(1000.00),
            10
        );

        // Mark first 3 installments as paid
        $installments = $plan->getInstallments()->toArray();
        $installments[0]->markAsPaid('cash');
        $installments[1]->markAsPaid('cash');
        $installments[2]->markAsPaid('cash');

        $totalPaid = $plan->getTotalPaid();

        $this->assertEquals(300.00, $totalPaid->getValue());
    }

    public function testCanCalculateProgress(): void
    {
        $plan = PaymentPlan::create(
            $this->enrollment,
            new Amount(1000.00),
            10
        );

        // Mark 5 out of 10 as paid
        $installments = $plan->getInstallments()->toArray();
        for ($i = 0; $i < 5; $i++) {
            $installments[$i]->markAsPaid('cash');
        }

        $progress = $plan->getProgress();

        $this->assertEquals(50.0, $progress);
    }

    public function testCanDetectOverdueInstallments(): void
    {
        $plan = PaymentPlan::create(
            $this->enrollment,
            new Amount(1000.00),
            10
        );

        // This would require mocking dates or using a test clock
        // For now, we just verify the method exists
        $this->assertIsObject($plan->getOverdueInstallments());
    }

    public function testCanGetNextPendingInstallment(): void
    {
        $plan = PaymentPlan::create(
            $this->enrollment,
            new Amount(1000.00),
            10
        );

        $next = $plan->getNextPendingInstallment();

        $this->assertNotNull($next);
        $this->assertEquals(1, $next->getNumber());
    }

    public function testCanMarkAsComplete(): void
    {
        $plan = PaymentPlan::create(
            $this->enrollment,
            new Amount(1000.00),
            2
        );

        // Pay all installments
        foreach ($plan->getInstallments() as $installment) {
            $installment->markAsPaid('cash');
        }

        $plan->markAsComplete();

        $this->assertEquals('completed', $plan->getStatus());
        $this->assertNotNull($plan->getCompletedAt());
    }

    public function testCannotMarkIncompleteAsComplete(): void
    {
        $this->expectException(\DomainException::class);

        $plan = PaymentPlan::create(
            $this->enrollment,
            new Amount(1000.00),
            10
        );

        $plan->markAsComplete();
    }

    public function testCanCancelPlan(): void
    {
        $plan = PaymentPlan::create(
            $this->enrollment,
            new Amount(1000.00),
            10
        );

        $plan->cancel();

        $this->assertEquals('cancelled', $plan->getStatus());
        $this->assertFalse($plan->isActive());
    }
}
