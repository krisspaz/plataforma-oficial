<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Teacher;
use App\Entity\Student;
use App\Entity\ParentEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-test-users',
    description: 'Creates test users for different roles',
)]
class CreateTestUsersCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Primero eliminamos usuarios existentes
        $this->entityManager->createQuery('DELETE FROM App\Entity\Student')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Teacher')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\ParentEntity')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        
        $output->writeln('Deleted existing test users');

        $users = [
            [
                'email' => 'admin@plataforma.com',
                'role' => 'ROLE_ADMIN',
                'firstName' => 'Administrador',
                'lastName' => 'Sistema',
                'phone' => '+502 5555-1000',
            ],
            [
                'email' => 'teacher@plataforma.com',
                'role' => 'ROLE_MAESTRO',
                'firstName' => 'Juan Carlos',
                'lastName' => 'López Martínez',
                'phone' => '+502 5555-2001',
                'specialization' => 'Matemáticas y Ciencias',
                'hireDate' => '2020-01-15',
                'birthDate' => '1985-05-20',
            ],
            [
                'email' => 'student@plataforma.com',
                'role' => 'ROLE_STUDENT',
                'firstName' => 'Pedro Antonio',
                'lastName' => 'García Hernández',
                'phone' => '+502 5555-3001',
                'studentBirthDate' => '2010-08-15',
            ],
            [
                'email' => 'parent@plataforma.com',
                'role' => 'ROLE_PADRE_FAMILIA',
                'firstName' => 'María Elena',
                'lastName' => 'Hernández de García',
                'phone' => '+502 5555-4001',
                'profession' => 'Ingeniera Civil',
                'workplace' => 'Constructora Guatemala S.A.',
            ],
        ];

        foreach ($users as $userData) {
            $user = new User();
            $user->setEmail($userData['email']);
            $user->setFirstName($userData['firstName']);
            $user->setLastName($userData['lastName']);
            $user->setPhone($userData['phone'] ?? null);
            $user->setRoles([$userData['role']]);
            
            $hashedPassword = $this->passwordHasher->hashPassword($user, 'password123');
            $user->setPassword($hashedPassword);

            $this->entityManager->persist($user);

            // Create associated entities based on role
            if ($userData['role'] === 'ROLE_MAESTRO') {
                $teacher = new Teacher();
                $teacher->setUser($user);
                $teacher->setSpecialization($userData['specialization']);
                $teacher->setHireDate(new \DateTime($userData['hireDate']));
                $teacher->setBirthDate(new \DateTime($userData['birthDate']));
                $this->entityManager->persist($teacher);
            } elseif ($userData['role'] === 'ROLE_STUDENT') {
                $student = new Student();
                $student->setUser($user);
                $student->setFirstName($userData['firstName']);
                $student->setLastName($userData['lastName']);
                $student->setEmail($userData['email']);
                $student->setBirthDate(new \DateTime($userData['studentBirthDate']));
                $student->setStatus('active');
                $this->entityManager->persist($student);
            } elseif ($userData['role'] === 'ROLE_PADRE_FAMILIA') {
                $parent = new ParentEntity();
                $parent->setUser($user);
                $parent->setPersonalId('DPI-' . rand(1000000000, 9999999999));
                $parent->setProfession($userData['profession']);
                $parent->setWorkplace($userData['workplace']);
                $this->entityManager->persist($parent);
            }

            $output->writeln("Created user: {$userData['email']} ({$userData['firstName']} {$userData['lastName']})");
        }

        $this->entityManager->flush();

        $output->writeln('');
        $output->writeln('✅ All test users created successfully!');
        $output->writeln('');
        $output->writeln('Credentials:');
        $output->writeln('  Email: [role]@plataforma.com');
        $output->writeln('  Password: password123');

        return Command::SUCCESS;
    }
}
