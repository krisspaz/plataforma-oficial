<?php

declare(strict_types=1);

namespace App\Domain\Payment\ValueObject;

use InvalidArgumentException;

/**
 * Value Object representing a monetary amount.
 * Immutable and self-validating.
 */
final class Amount
{
    private readonly float $value;
    private readonly string $currency;

    public function __construct(float $value, string $currency = 'GTQ')
    {
        if ($value < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }

        $this->value = round($value, 2);
        $this->currency = strtoupper($currency);
    }

    public static function fromString(string $amount, string $currency = 'GTQ'): self
    {
        return new self((float) $amount, $currency);
    }

    public static function zero(string $currency = 'GTQ'): self
    {
        return new self(0.0, $currency);
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function add(Amount $other): self
    {
        $this->assertSameCurrency($other);
        return new self($this->value + $other->value, $this->currency);
    }

    public function subtract(Amount $other): self
    {
        $this->assertSameCurrency($other);
        $result = $this->value - $other->value;

        if ($result < 0) {
            throw new InvalidArgumentException('Subtraction would result in negative amount');
        }

        return new self($result, $this->currency);
    }

    public function multiply(float $factor): self
    {
        return new self($this->value * $factor, $this->currency);
    }

    public function divide(int $divisor): self
    {
        if ($divisor <= 0) {
            throw new InvalidArgumentException('Divisor must be positive');
        }

        return new self($this->value / $divisor, $this->currency);
    }

    public function isGreaterThan(Amount $other): bool
    {
        $this->assertSameCurrency($other);
        return $this->value > $other->value;
    }

    public function isLessThan(Amount $other): bool
    {
        $this->assertSameCurrency($other);
        return $this->value < $other->value;
    }

    public function equals(Amount $other): bool
    {
        return $this->value === $other->value && $this->currency === $other->currency;
    }

    public function isZero(): bool
    {
        return $this->value === 0.0;
    }

    public function format(): string
    {
        return sprintf('%s %.2f', $this->currency, $this->value);
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }

    private function assertSameCurrency(Amount $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                sprintf('Cannot operate on different currencies: %s vs %s', $this->currency, $other->currency)
            );
        }
    }
}
