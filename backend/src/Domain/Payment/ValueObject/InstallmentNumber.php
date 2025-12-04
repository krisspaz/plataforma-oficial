<?php

declare(strict_types=1);

namespace App\Domain\Payment\ValueObject;

use InvalidArgumentException;

/**
 * Value Object representing an installment number.
 */
final class InstallmentNumber
{
    private readonly int $number;
    private readonly int $total;

    public function __construct(int $number, int $total)
    {
        if ($number < 1) {
            throw new InvalidArgumentException('Installment number must be at least 1');
        }

        if ($total < 1) {
            throw new InvalidArgumentException('Total installments must be at least 1');
        }

        if ($number > $total) {
            throw new InvalidArgumentException(
                sprintf('Installment number (%d) cannot exceed total installments (%d)', $number, $total)
            );
        }

        $this->number = $number;
        $this->total = $total;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function isFirst(): bool
    {
        return $this->number === 1;
    }

    public function isLast(): bool
    {
        return $this->number === $this->total;
    }

    public function getRemaining(): int
    {
        return $this->total - $this->number;
    }

    public function next(): ?self
    {
        if ($this->isLast()) {
            return null;
        }

        return new self($this->number + 1, $this->total);
    }

    public function format(): string
    {
        return sprintf('%d/%d', $this->number, $this->total);
    }

    public function __toString(): string
    {
        return $this->format();
    }

    public function equals(InstallmentNumber $other): bool
    {
        return $this->number === $other->number && $this->total === $other->total;
    }
}
