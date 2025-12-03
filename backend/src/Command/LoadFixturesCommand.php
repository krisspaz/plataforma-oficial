<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Entity\Student;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:fixtures:load',
    description: 'Carga datos de prueba en la base de datos',
)]
class LoadFixturesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Cargando Datos de Prueba');

        // Limpiar datos existentes
        $io->section('Limpiando base de datos...');
        $this->entityManager->createQuery('DELETE FROM App\Entity\Student')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();

        // Crear usuarios de prueba
        $io->section('Creando usuarios de prueba...');

        $users = [
            [
                'email' => 'admin@school.com',
                'password' => 'Admin123!',
                'roles' => ['ROLE_ADMIN'],
                'firstName' => 'Administrador',
                'lastName' => 'Sistema'
            ],
            [
                'email' => 'coordinador@school.com',
                'password' => 'Coord123!',
                'roles' => ['ROLE_COORDINATOR'],
                'firstName' => 'María',
                'lastName' => 'Coordinadora'
            ],
            [
                'email' => 'maestro@school.com',
                'password' => 'Teacher123!',
                'roles' => ['ROLE_TEACHER'],
                'firstName' => 'Juan',
                'lastName' => 'Maestro'
            ],
            [
                'email' => 'secretaria@school.com',
                'password' => 'Secret123!',
                'roles' => ['ROLE_SECRETARY'],
                'firstName' => 'Ana',
                'lastName' => 'Secretaria'
            ],
            [
                'email' => 'padre@school.com',
                'password' => 'Parent123!',
                'roles' => ['ROLE_PARENT'],
                'firstName' => 'Carlos',
                'lastName' => 'Padre'
            ],
        ];

        foreach ($users as $userData) {
            $user = new User();
            $user->setEmail($userData['email']);
            $user->setRoles($userData['roles']);
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, $userData['password'])
            );

            $this->entityManager->persist($user);

            $io->writeln(sprintf(
                '✓ Usuario creado: <info>%s</info> (password: <comment>%s</comment>)',
                $userData['email'],
                $userData['password']
            ));
        }

        // Crear estudiantes de prueba
        $io->section('Creando estudiantes de prueba...');

        $students = [
            [
                'firstName' => 'Pedro',
                'lastName' => 'García',
                'email' => 'pedro.garcia@student.school.com',
                'birthDate' => new \DateTime('2010-05-15'),
                'status' => 'active'
            ],
            [
                'firstName' => 'Laura',
                'lastName' => 'Martínez',
                'email' => 'laura.martinez@student.school.com',
                'birthDate' => new \DateTime('2011-08-22'),
                'status' => 'active'
            ],
            [
                'firstName' => 'Miguel',
                'lastName' => 'López',
                'email' => 'miguel.lopez@student.school.com',
                'birthDate' => new \DateTime('2010-12-03'),
                'status' => 'active'
            ],
            [
                'firstName' => 'Sofia',
                'lastName' => 'Hernández',
                'email' => 'sofia.hernandez@student.school.com',
                'birthDate' => new \DateTime('2011-03-18'),
                'status' => 'active'
            ],
            [
                'firstName' => 'Diego',
                'lastName' => 'Ramírez',
                'email' => 'diego.ramirez@student.school.com',
                'birthDate' => new \DateTime('2010-09-27'),
                'status' => 'active'
            ],
        ];

        foreach ($students as $studentData) {
            $student = new Student();
            $student->setFirstName($studentData['firstName']);
            $student->setLastName($studentData['lastName']);
            $student->setEmail($studentData['email']);
            $student->setBirthDate($studentData['birthDate']);
            $student->setStatus($studentData['status']);
            $student->setCreatedAt(new \DateTime());

            $this->entityManager->persist($student);

            $io->writeln(sprintf(
                '✓ Estudiante creado: <info>%s %s</info> (%s)',
                $studentData['firstName'],
                $studentData['lastName'],
                $studentData['email']
            ));
        }

        $this->entityManager->flush();

        $io->success('Datos de prueba cargados exitosamente!');

        $io->section('Credenciales de Acceso:');
        $io->table(
            ['Rol', 'Email', 'Password'],
            [
                ['Admin', 'admin@school.com', 'Admin123!'],
                ['Coordinador', 'coordinador@school.com', 'Coord123!'],
                ['Maestro', 'maestro@school.com', 'Teacher123!'],
                ['Secretaria', 'secretaria@school.com', 'Secret123!'],
                ['Padre', 'padre@school.com', 'Parent123!'],
            ]
        );

        return Command::SUCCESS;
    }
}
