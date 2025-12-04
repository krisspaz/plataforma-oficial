<?php

declare(strict_types=1);

namespace App\Domain\Exception;

class StudentNotFoundException extends \DomainException
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Student with ID %d not found', $id));
    }

    public static function withEmail(string $email): self
    {
        return new self(sprintf('Student with email "%s" not found', $email));
    }
}
