<?php

declare(strict_types=1);

namespace App\Domain\Exception;

class DuplicateStudentException extends \DomainException
{
    public static function withEmail(string $email): self
    {
        return new self(sprintf('A student with email "%s" already exists', $email));
    }

    public static function withPersonalId(string $personalId): self
    {
        return new self(sprintf('A student with personal ID "%s" already exists', $personalId));
    }
}
