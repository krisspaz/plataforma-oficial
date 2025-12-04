<?php

declare(strict_types=1);

namespace App\Application\Student\Command;

use App\Application\Student\DTO\CreateStudentDTO;
use App\Domain\Student\StudentRepositoryInterface;
use App\Domain\Student\ValueObject\Email;
use App\Domain\Student\Event\StudentCreatedEvent;
use App\Entity\Student;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Symfony\Component\Mailer\MailerInterface;

final readonly class CreateStudentCommandHandler
{
    public function __construct(
        private StudentRepositoryInterface $studentRepository,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private EventDispatcherInterface $eventDispatcher,
        private ValidatorInterface $validator,
        private MailerInterface $mailer,
    ) {}

    public function handle(CreateStudentDTO $dto): Student
    {
        // Validar DTO
        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            throw new ValidationFailedException($dto, $violations);
        }

        // Verificar si el email ya existe
        $email = Email::fromString($dto->email);
        if ($this->studentRepository->findByEmail($email) !== null) {
            throw new \DomainException('Student with this email already exists');
        }

        // Crear usuario
        $user = new User();
        $user->setEmail($dto->email);
        $user->setFirstName($dto->firstName);
        $user->setLastName($dto->lastName);
        $user->setPhone($dto->phone);
        $user->setRoles(['ROLE_STUDENT']);

        // Generar contraseña temporal
        $temporaryPassword = $this->generateTemporaryPassword();
        $user->setPassword($this->passwordHasher->hashPassword($user, $temporaryPassword));

        // Crear estudiante
        $student = new Student();
        $student->setUser($user);

        if ($dto->birthDate) {
            $student->setBirthDate(new \DateTime($dto->birthDate));
        }

        $student->setGender($dto->gender);
        $student->setNationality($dto->nationality);
        $student->setAddress($dto->address);
        $student->setEmergencyContact($dto->emergencyContact);

        // Persistir en la base de datos
        $this->entityManager->persist($user);
        $this->entityManager->persist($student);
        $this->entityManager->flush();

        // Enviar correo con contraseña temporal
        $this->sendWelcomeEmail($dto->email, $temporaryPassword);

        // Dispatch del evento
        $event = new StudentCreatedEvent(
            studentId: $student->getId(),
            email: $email,
            firstName: $dto->firstName,
            lastName: $dto->lastName
        );
        $this->eventDispatcher->dispatch($event, $event->getEventName());

        return $student;
    }

    private function generateTemporaryPassword(): string
    {
        return bin2hex(random_bytes(8)); // 16 caracteres hexadecimales
    }

    private function sendWelcomeEmail(string $to, string $temporaryPassword): void
    {
        $email = (new SymfonyEmail())
            ->from('info@colegiooxford.edu.gt')
            ->to($to)
            ->subject('Bienvenido al Colegio Oxford')
            ->html(sprintf(
                '<p>Bienvenido al Colegio Oxford.</p>
                 <p>Tu contraseña temporal es: <strong>%s</strong></p>
                 <p>Por favor, cámbiala después de iniciar sesión.</p>',
                $temporaryPassword
            ));

        $this->mailer->send($email);
    }
}
