<?php

declare(strict_types=1);

namespace App\Application\Student\Command;

use App\Application\Student\DTO\CreateStudentDTO;
use App\Domain\Student\StudentRepositoryInterface;
use App\Domain\Student\ValueObject\Email;
use App\Domain\Student\ValueObject\PersonName;
use App\Domain\Student\Event\StudentCreatedEvent;
use App\Entity\Student;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final readonly class CreateStudentCommandHandler
{
    public function __construct(
        private StudentRepositoryInterface $studentRepository,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private EventDispatcherInterface $eventDispatcher,
        private ValidatorInterface $validator,
    ) {}

    public function handle(CreateStudentDTO $dto): Student
    {
        // Validate DTO
        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            throw new ValidationFailedException($dto, $violations);
        }

        // Check if email already exists
        $email = Email::fromString($dto->email);
        if ($this->studentRepository->findByEmail($email) !== null) {
            throw new \DomainException('Student with this email already exists');
        }

        // Create User entity
        $user = new User();
        $user->setEmail($dto->email);
        $user->setFirstName($dto->firstName);
        $user->setLastName($dto->lastName);
        $user->setPhone($dto->phone);
        $user->setRoles(['ROLE_STUDENT']);

        // Generate temporary password
        $temporaryPassword = $this->generateTemporaryPassword();
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $temporaryPassword)
        );

        // Create Student entity
        $student = new Student();
        $student->setUser($user);

        if ($dto->birthDate) {
            $student->setBirthDate(new \DateTime($dto->birthDate));
        }

        $student->setGender($dto->gender);
        $student->setNationality($dto->nationality);
        $student->setAddress($dto->address);
        $student->setEmergencyContact($dto->emergencyContact);

        // Persist
        $this->entityManager->persist($user);
        $this->entityManager->persist($student);
        $this->entityManager->flush();

        // Dispatch domain event
        $event = new StudentCreatedEvent(
            studentId: $student->getId(),
            email: $email,
            firstName: $dto->firstName,
            lastName: $dto->lastName
        );
        $this->eventDispatcher->dispatch($event, $event->getEventName());

        // TODO: Send welcome email with temporary password

        return $student;
    }

    private function generateTemporaryPassword(): string
    {
        return bin2hex(random_bytes(8));
    }
}
