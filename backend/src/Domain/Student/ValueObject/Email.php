<?php

declare(strict_types=1);

namespace App\Domain\Student\ValueObject;

use InvalidArgumentException;

final readonly class Email
{
    private function __construct(
        public string $value
    ) {
        $this->validate();
    }

    public static function fromString(string $email): self
    {
        return new self($email);
    }

    private function validate(): void
    {
        if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                sprintf('"%s" is not a valid email address', $this->value)
            );
        }

        // Additional validation: check domain exists
        $domain = substr(strrchr($this->value, '@'), 1);
        if (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
            throw new InvalidArgumentException(
                sprintf('Email domain "%s" does not exist', $domain)
            );
        }
    }

    public function equals(self $other): bool
    {
        return strtolower($this->value) === strtolower($other->value);
    }

    public function getDomain(): string
    {
        return substr(strrchr($this->value, '@'), 1);
    }

    public function getLocalPart(): string
    {
        return strstr($this->value, '@', true);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
