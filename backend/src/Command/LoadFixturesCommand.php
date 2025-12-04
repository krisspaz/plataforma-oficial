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
    description: 'Carga datos de prueba en la base de datos para todos los roles',
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

        $io->section('Creando usuarios de prueba...');

        // Usuarios administrativos y roles especiales
        $usersData = [
            ['email' => 'admin@school.com', 'password' => 'Admin123!', 'firstName' => 'Admin', 'lastName' => 'Sistema', 'roles' => ['ROLE_ADMIN']],
            ['email' => 'secretaria@school.com', 'password' => 'Secret123!', 'firstName' => 'Ana', 'lastName' => 'Secretaria', 'roles' => ['ROLE_SECRETARY']],
            ['email' => 'coordinador@school.com', 'password' => 'Coord123!', 'firstName' => 'María', 'lastName' => 'Coordinadora', 'roles' => ['ROLE_COORDINATOR']],
            ['email' => 'administracion@school.com', 'password' => 'Adminis123!', 'firstName' => 'Luis', 'lastName' => 'Gerente', 'roles' => ['ROLE_MANAGER']],
            ['email' => 'maestro1@school.com', 'password' => 'Teacher123!', 'firstName' => 'Juan', 'lastName' => 'Maestro', 'roles' => ['ROLE_TEACHER']],
            ['email' => 'padre1@school.com', 'password' => 'Parent123!', 'firstName' => 'Carlos', 'lastName' => 'Padre', 'roles' => ['ROLE_PARENT']],
        ];

        foreach ($usersData as $data) {
            $user = new User();
            $user->setEmail($data['email']);
            $user->setFirstName($data['firstName']);
            $user->setLastName($data['lastName']);
            $user->setRoles($data['roles']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
            $this->entityManager->persist($user);

            $io->writeln(sprintf(
                '✓ Usuario creado: <info>%s</info> (password: <comment>%s</comment>)',
                $data['email'],
                $data['password']
            ));
        }

        // Crear alumnos de prueba
        $io->section('Creando alumnos de prueba...');
        $studentsData = [
            ['firstName' => 'Pedro', 'lastName' => 'García', 'email' => 'pedro.garcia@student.school.com', 'birthDate' => '2010-05-15', 'gender' => 'M', 'password' => 'Pedro123!'],
            ['firstName' => 'Laura', 'lastName' => 'Martínez', 'email' => 'laura.martinez@student.school.com', 'birthDate' => '2011-08-22', 'gender' => 'F', 'password' => 'Laura123!'],
            ['firstName' => 'Miguel', 'lastName' => 'López', 'email' => 'miguel.lopez@student.school.com', 'birthDate' => '2010-12-03', 'gender' => 'M', 'password' => 'Miguel123!'],
            ['firstName' => 'Sofía', 'lastName' => 'Hernández', 'email' => 'sofia.hernandez@student.school.com', 'birthDate' => '2011-03-18', 'gender' => 'F', 'password' => 'Sofia123!'],
            ['firstName' => 'Diego', 'lastName' => 'Ramírez', 'email' => 'diego.ramirez@student.school.com', 'birthDate' => '2010-09-27', 'gender' => 'M', 'password' => 'Diego123!'],
        ];

        foreach ($studentsData as $data) {
            // Crear usuario del alumno
            $user = new User();
            $user->setEmail($data['email']);
            $user->setFirstName($data['firstName']);
            $user->setLastName($data['lastName']);
            $user->setRoles(['ROLE_STUDENT']);
            $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));
            $this->entityManager->persist($user);

            // Crear entidad Student
            $student = new Student();
            $student->setUser($user);
            $student->setBirthDate(new \DateTime($data['birthDate']));
            $student->setGender($data['gender']);
            $student->setCreatedAt(new \DateTime());
            $this->entityManager->persist($student);

            $io->writeln(sprintf(
                '✓ Alumno creado: <info>%s %s</info> (%s, password: %s)',
                $data['firstName'],
                $data['lastName'],
                $data['email'],
                $data['password']
            ));
        }

        $this->entityManager->flush();
        $io->success('Todos los roles y usuarios de prueba han sido cargados exitosamente!');

        return Command::SUCCESS;
    }
}
