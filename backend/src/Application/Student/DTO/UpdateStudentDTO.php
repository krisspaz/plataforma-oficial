<?php

declare(strict_types=1);

namespace App\Application\Student\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateStudentDTO
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Positive]
        public int $id,

        #[Assert\Email(message: 'Invalid email format')]
        public ?string $email = null,

        #[Assert\Length(min: 2, max: 50)]
        public ?string $firstName = null,

        #[Assert\Length(min: 2, max: 50)]
        public ?string $lastName = null,

        #[Assert\Length(max: 20)]
        public ?string $phone = null,

        #[Assert\Date]
        public ?string $birthDate = null,

        #[Assert\Choice(choices: ['M', 'F', 'Other'])]
        public ?string $gender = null,

        #[Assert\Length(max: 50)]
        public ?string $nationality = null,

        #[Assert\Length(max: 500)]
        public ?string $address = null,

        public ?string $emergencyContact = null,
    ) {}
}
