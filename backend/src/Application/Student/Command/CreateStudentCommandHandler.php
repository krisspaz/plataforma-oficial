<?php

declare(strict_types=1);

namespace App\Application\Student\Command;

use App\Application\Student\DTO\CreateStudentDTO;
use App\Domain\Student\StudentRepositoryInterface;
use App\Domain\Student\Event\StudentCreatedEvent;
use App\Entity\Student;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

final readonly class CreateStudentCommandHandler
{
    public function __construct(
        private StudentRepositoryInterface $studentRepository,
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private EventDispatcherInterface $eventDispatcher,
        private ValidatorInterface $validator,
        private MailerInterface $mailer,
        private string $senderEmail // Configurado desde services.yaml o env
    ) {}

    public function handle(CreateStudentDTO $dto): Student
    {
        // 1️⃣ Validar DTO
        $violations = $this->validator->validate($dto);
        if (count($violations) > 0) {
            throw new ValidationFailedException($dto, $violations);
        }

        // 2️⃣ Verificar email único
        if ($this->studentRepository->findByEmail($dto->email) !== null) {
            throw new \DomainException('A student with this email already exists.');
        }

        // 3️⃣ Crear entidad User
        $user = new User();
        $user->setEmail($dto->email)
             ->setFirstName($dto->firstName)
             ->setLastName($dto->lastName)
             ->setPhone($dto->phone)
             ->setRoles(['ROLE_STUDENT']);

        // 4️⃣ Generar contraseña segura y hash
        $temporaryPassword = $this->generateSecurePassword();
        $user->setPassword($this->passwordHasher->hashPassword($user, $temporaryPassword));

        // 5️⃣ Crear entidad Student
        $student = new Student();
        $student->setUser($user)
                ->setGender($dto->gender)
                ->setNationality($dto->nationality)
                ->setAddress($dto->address)
                ->setEmergencyContact($dto->emergencyContact);

        if (!empty($dto->birthDate)) {
            try {
                $student->setBirthDate(new \DateTimeImmutable($dto->birthDate));
            } catch (\Exception $e) {
                throw new \InvalidArgumentException('Invalid birthDate format. Expecting Y-m-d.', 0, $e);
            }
        }

        // 6️⃣ Persistir entidades
        $this->entityManager->persist($user);
        $this->entityManager->persist($student);
        $this->entityManager->flush();

        // 7️⃣ Despachar evento
        $event = new StudentCreatedEvent(
            studentId: $student->getId(),
            email: $dto->email,
            firstName: $dto->firstName,
            lastName: $dto->lastName
        );
        $this->eventDispatcher->dispatch($event, $event->getEventName());

        // 8️⃣ Enviar correo de bienvenida con contraseña temporal
        $this->sendWelcomeEmail($user->getEmail(), $dto->firstName, $temporaryPassword);

        return $student;
    }

    private function generateSecurePassword(int $length = 12): string
    {
        // Genera contraseña fuerte con letras mayúsculas/minúsculas, números y símbolos
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+';
        $password = '';
        $maxIndex = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $maxIndex)];
        }
        return $password;
    }

    private function sendWelcomeEmail(string $email, string $firstName, string $password): void
    {
        $message = (new Email())
            ->from(new Address($this->senderEmail, 'School Administration'))
            ->to($email)
            ->subject('Welcome to the School Platform')
            ->html(
                sprintf(
                    '<p>Hello %s,</p><p>Your account has been created.</p><p><strong>Email:</strong> %s<br><strong>Password:</strong> %s</p><p>Please change your password after logging in for the first time.</p>',
                    htmlspecialchars($firstName),
                    htmlspecialchars($email),
                    htmlspecialchars($password)
                )
            );

        $this->mailer->send($message);
    }
}
