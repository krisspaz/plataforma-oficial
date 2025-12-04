<?php

declare(strict_types=1);

namespace App\Domain\Payment\ValueObject;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Value Object representing a due date with business logic.
 */
final class DueDate
{
    private readonly DateTimeImmutable $date;

    public function __construct(DateTimeImmutable $date)
    {
        $this->date = $date->setTime(23, 59, 59);
    }

    public static function fromString(string $date): self
    {
        $parsed = DateTimeImmutable::createFromFormat('Y-m-d', $date);

        if ($parsed === false) {
            throw new InvalidArgumentException(sprintf('Invalid date format: %s. Expected Y-m-d', $date));
        }

        return new self($parsed);
    }

    public static function fromDateTime(\DateTimeInterface $date): self
    {
        return new self(DateTimeImmutable::createFromInterface($date));
    }

    public static function today(): self
    {
        return new self(new DateTimeImmutable('today'));
    }

    public static function inDays(int $days): self
    {
        return new self(new DateTimeImmutable(sprintf('+%d days', $days)));
    }

    public static function nextMonth(int $day = 1): self
    {
        $date = new DateTimeImmutable(sprintf('first day of next month'));
        $date = $date->setDate((int)$date->format('Y'), (int)$date->format('m'), min($day, (int)$date->format('t')));

        return new self($date);
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function isOverdue(): bool
    {
        return $this->date < new DateTimeImmutable('today');
    }

    public function isDueToday(): bool
    {
        return $this->date->format('Y-m-d') === (new DateTimeImmutable())->format('Y-m-d');
    }

    public function getDaysUntilDue(): int
    {
        $today = new DateTimeImmutable('today');
        $diff = $today->diff($this->date);

        return $diff->invert ? -$diff->days : $diff->days;
    }

    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        return abs($this->getDaysUntilDue());
    }

    public function addMonths(int $months): self
    {
        return new self($this->date->modify(sprintf('+%d months', $months)));
    }

    public function format(string $format = 'Y-m-d'): string
    {
        return $this->date->format($format);
    }

    public function equals(DueDate $other): bool
    {
        return $this->date->format('Y-m-d') === $other->date->format('Y-m-d');
    }

    public function __toString(): string
    {
        return $this->format();
    }

    public function getOverdueLevel(): string
    {
        $days = $this->getDaysOverdue();

        if ($days === 0) {
            return 'current';
        }

        if ($days <= 15) {
            return 'warning';
        }

        if ($days <= 30) {
            return 'danger';
        }

        return 'critical';
    }
}
