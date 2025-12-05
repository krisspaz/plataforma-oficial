<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Payment\ValueObject;

use App\Domain\Payment\ValueObject\PaymentAmount;
use PHPUnit\Framework\TestCase;

class PaymentAmountTest extends TestCase
{
    public function testCreateWithValidAmount(): void
    {
        $amount = new PaymentAmount(100.50);

        $this->assertEquals(100.50, $amount->getValue());
    }

    public function testThrowsExceptionForNegativeAmount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be positive');

        new PaymentAmount(-50.00);
    }

    public function testThrowsExceptionForZeroAmount(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new PaymentAmount(0);
    }

    public function testEquals(): void
    {
        $amount1 = new PaymentAmount(100.00);
        $amount2 = new PaymentAmount(100.00);
        $amount3 = new PaymentAmount(200.00);

        $this->assertTrue($amount1->equals($amount2));
        $this->assertFalse($amount1->equals($amount3));
    }

    public function testAdd(): void
    {
        $amount1 = new PaymentAmount(100.00);
        $amount2 = new PaymentAmount(50.00);

        $result = $amount1->add($amount2);

        $this->assertEquals(150.00, $result->getValue());
    }

    public function testSubtract(): void
    {
        $amount1 = new PaymentAmount(100.00);
        $amount2 = new PaymentAmount(30.00);

        $result = $amount1->subtract($amount2);

        $this->assertEquals(70.00, $result->getValue());
    }

    public function testSubtractThrowsExceptionWhenResultNegative(): void
    {
        $amount1 = new PaymentAmount(50.00);
        $amount2 = new PaymentAmount(100.00);

        $this->expectException(\InvalidArgumentException::class);

        $amount1->subtract($amount2);
    }

    public function testFormattedWithCurrency(): void
    {
        $amount = new PaymentAmount(1250.50);

        $this->assertEquals('Q1,250.50', $amount->formatted('GTQ'));
        $this->assertEquals('$1,250.50', $amount->formatted('USD'));
    }
}
