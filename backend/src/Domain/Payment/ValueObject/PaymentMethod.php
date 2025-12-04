<?php

declare(strict_types=1);

namespace App\Domain\Payment\ValueObject;

use InvalidArgumentException;

/**
 * Value Object representing a payment method.
 */
final class PaymentMethod
{
    public const CASH = 'cash';
    public const INSTALLMENTS = 'installments';
    public const CARD = 'card';
    public const TRANSFER = 'transfer';
    public const STRIPE = 'stripe';
    public const PAYPAL = 'paypal';
    public const BAC = 'bac';

    private const VALID_METHODS = [
        self::CASH,
        self::INSTALLMENTS,
        self::CARD,
        self::TRANSFER,
        self::STRIPE,
        self::PAYPAL,
        self::BAC,
    ];

    private readonly string $value;

    public function __construct(string $value)
    {
        $normalizedValue = strtolower(trim($value));

        if (!in_array($normalizedValue, self::VALID_METHODS, true)) {
            throw new InvalidArgumentException(
                sprintf('Invalid payment method: %s. Valid methods are: %s', $value, implode(', ', self::VALID_METHODS))
            );
        }

        $this->value = $normalizedValue;
    }

    public static function cash(): self
    {
        return new self(self::CASH);
    }

    public static function installments(): self
    {
        return new self(self::INSTALLMENTS);
    }

    public static function card(): self
    {
        return new self(self::CARD);
    }

    public static function transfer(): self
    {
        return new self(self::TRANSFER);
    }

    public static function stripe(): self
    {
        return new self(self::STRIPE);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isCash(): bool
    {
        return $this->value === self::CASH;
    }

    public function isInstallments(): bool
    {
        return $this->value === self::INSTALLMENTS;
    }

    public function isOnline(): bool
    {
        return in_array($this->value, [self::STRIPE, self::PAYPAL, self::BAC], true);
    }

    public function equals(PaymentMethod $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function getDisplayName(): string
    {
        return match ($this->value) {
            self::CASH => 'Efectivo',
            self::INSTALLMENTS => 'Cuotas',
            self::CARD => 'Tarjeta',
            self::TRANSFER => 'Transferencia',
            self::STRIPE => 'Stripe',
            self::PAYPAL => 'PayPal',
            self::BAC => 'BAC',
            default => ucfirst($this->value),
        };
    }
}
