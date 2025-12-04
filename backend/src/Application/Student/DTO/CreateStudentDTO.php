<?php

declare(strict_types=1);

namespace App\Application\Student\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateStudentDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,

        #[Assert\NotBlank]
        public string $firstName,

        #[Assert\NotBlank]
        public string $lastName,

        public ?string $phone = null,
        public ?string $birthDate = null,
        public ?string $gender = null,
        public ?string $nationality = null,
        public ?string $address = null,
        public ?string $emergencyContact = null,
    ) {}
}
