<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Payment\ValueObject;

use App\Domain\Payment\ValueObject\Amount;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AmountTest extends TestCase
{
    public function testCanCreateAmount(): void
    {
        $amount = new Amount(100.50, 'GTQ');

        $this->assertEquals(100.50, $amount->getValue());
        $this->assertEquals('GTQ', $amount->getCurrency());
    }

    public function testCannotCreateNegativeAmount(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount cannot be negative');

        new Amount(-10.00);
    }

    public function testCanAddAmounts(): void
    {
        $amount1 = new Amount(100.00);
        $amount2 = new Amount(50.00);

        $result = $amount1->add($amount2);

        $this->assertEquals(150.00, $result->getValue());
    }

    public function testCanSubtractAmounts(): void
    {
        $amount1 = new Amount(100.00);
        $amount2 = new Amount(30.00);

        $result = $amount1->subtract($amount2);

        $this->assertEquals(70.00, $result->getValue());
    }

    public function testCannotSubtractToNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $amount1 = new Amount(50.00);
        $amount2 = new Amount(100.00);

        $amount1->subtract($amount2);
    }

    public function testCanMultiplyAmount(): void
    {
        $amount = new Amount(100.00);
        $result = $amount->multiply(1.5);

        $this->assertEquals(150.00, $result->getValue());
    }

    public function testCanDivideAmount(): void
    {
        $amount = new Amount(100.00);
        $result = $amount->divide(4);

        $this->assertEquals(25.00, $result->getValue());
    }

    public function testCannotDivideByZero(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $amount = new Amount(100.00);
        $amount->divide(0);
    }

    public function testCanCompareAmounts(): void
    {
        $amount1 = new Amount(100.00);
        $amount2 = new Amount(50.00);
        $amount3 = new Amount(100.00);

        $this->assertTrue($amount1->isGreaterThan($amount2));
        $this->assertTrue($amount2->isLessThan($amount1));
        $this->assertTrue($amount1->equals($amount3));
    }

    public function testCannotOperateOnDifferentCurrencies(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot operate on different currencies');

        $amount1 = new Amount(100.00, 'GTQ');
        $amount2 = new Amount(50.00, 'USD');

        $amount1->add($amount2);
    }

    public function testCanCheckIfZero(): void
    {
        $zero = Amount::zero();
        $nonZero = new Amount(10.00);

        $this->assertTrue($zero->isZero());
        $this->assertFalse($nonZero->isZero());
    }

    public function testCanFormatAmount(): void
    {
        $amount = new Amount(1234.56, 'GTQ');

        $formatted = $amount->format();

        $this->assertEquals('GTQ 1234.56', $formatted);
    }

    public function testRoundsToTwoDecimals(): void
    {
        $amount = new Amount(10.999);

        $this->assertEquals(11.00, $amount->getValue());
    }
}
