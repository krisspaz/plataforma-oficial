<?php

declare(strict_types=1);

namespace App\Application\Student\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateStudentDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Email is required')]
        #[Assert\Email(message: 'Invalid email format')]
        public string $email,

        #[Assert\NotBlank(message: 'First name is required')]
        #[Assert\Length(
            min: 2,
            max: 50,
            minMessage: 'First name must be at least {{ limit }} characters',
            maxMessage: 'First name cannot exceed {{ limit }} characters'
        )]
        #[Assert\Regex(
            pattern: "/^[\p{L}\s\-']+$/u",
            message: 'First name contains invalid characters'
        )]
        public string $firstName,

        #[Assert\NotBlank(message: 'Last name is required')]
        #[Assert\Length(
            min: 2,
            max: 50,
            minMessage: 'Last name must be at least {{ limit }} characters',
            maxMessage: 'Last name cannot exceed {{ limit }} characters'
        )]
        #[Assert\Regex(
            pattern: "/^[\p{L}\s\-']+$/u",
            message: 'Last name contains invalid characters'
        )]
        public string $lastName,

        #[Assert\Length(max: 20)]
        #[Assert\Regex(
            pattern: '/^[0-9\-\+\(\)\s]+$/',
            message: 'Phone number contains invalid characters'
        )]
        public ?string $phone = null,

        #[Assert\Date]
        public ?string $birthDate = null,

        #[Assert\Choice(choices: ['M', 'F', 'Other'])]
        public ?string $gender = null,

        #[Assert\Length(max: 50)]
        public ?string $nationality = null,

        #[Assert\Length(max: 500)]
        public ?string $address = null,

        #[Assert\Type('array')]
        public ?array $emergencyContact = null,
    ) {}
}
