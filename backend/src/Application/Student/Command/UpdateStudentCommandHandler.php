<?php

declare(strict_types=1);

namespace App\Application\Student\Command;

use App\Application\Student\DTO\UpdateStudentDTO;
use App\Domain\Exception\StudentNotFoundException;
use App\Domain\Student\StudentRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use App\Entity\Student;

final readonly class UpdateStudentCommandHandler
{
    public function __construct(
        private StudentRepositoryInterface $studentRepository,
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
    ) {}

    public function __invoke(UpdateStudentDTO $dto): Student
    {
        // Validar DTO
        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            throw new ValidationFailedException($dto, $violations);
        }

        $student = $this->studentRepository->findById($dto->id);

        if (!$student) {
            throw StudentNotFoundException::withId($dto->id);
        }

        $user = $student->getUser();

        // Actualizar solo los campos proporcionados
        if ($dto->email !== null) {
            $user->setEmail($dto->email);
        }

        if ($dto->firstName !== null) {
            $user->setFirstName($dto->firstName);
        }

        if ($dto->lastName !== null) {
            $user->setLastName($dto->lastName);
        }

        if ($dto->phone !== null) {
            $user->setPhone($dto->phone);
        }

        if ($dto->birthDate !== null) {
            $student->setBirthDate(new \DateTimeImmutable($dto->birthDate));
        }

        if ($dto->gender !== null) {
            $student->setGender($dto->gender);
        }

        if ($dto->nationality !== null) {
            $student->setNationality($dto->nationality);
        }

        if ($dto->address !== null) {
            $student->setAddress($dto->address);
        }

        if ($dto->emergencyContact !== null) {
            $student->setEmergencyContact($dto->emergencyContact);
        }

        $this->entityManager->flush();

        return $student;
    }
}
