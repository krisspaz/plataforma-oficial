<?php

declare(strict_types=1);

namespace App\Domain\Student\ValueObject;

use InvalidArgumentException;

final readonly class StudentId
{
    private function __construct(
        public int $value
    ) {
        $this->validate();
    }

    public static function fromInt(int $id): self
    {
        return new self($id);
    }

    public static function fromString(string $id): self
    {
        if (!is_numeric($id)) {
            throw new InvalidArgumentException('Student ID must be numeric');
        }

        return new self((int) $id);
    }

    private function validate(): void
    {
        if ($this->value <= 0) {
            throw new InvalidArgumentException('Student ID must be positive');
        }
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
