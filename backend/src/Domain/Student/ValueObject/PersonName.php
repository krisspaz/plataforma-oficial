<?php

declare(strict_types=1);

namespace App\Domain\Student\ValueObject;

use InvalidArgumentException;

final readonly class PersonName
{
    private const MIN_LENGTH = 2;
    private const MAX_LENGTH = 50;

    private function __construct(
        public string $value
    ) {
        $this->validate();
    }

    public static function fromString(string $name): self
    {
        return new self(trim($name));
    }

    private function validate(): void
    {
        $length = mb_strlen($this->value);

        if ($length < self::MIN_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Name must be at least %d characters long', self::MIN_LENGTH)
            );
        }

        if ($length > self::MAX_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Name must not exceed %d characters', self::MAX_LENGTH)
            );
        }

        // Only allow letters, spaces, hyphens, and apostrophes
        if (!preg_match("/^[\p{L}\s\-']+$/u", $this->value)) {
            throw new InvalidArgumentException(
                'Name contains invalid characters'
            );
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
