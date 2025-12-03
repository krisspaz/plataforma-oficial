<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Student;
use App\Entity\ParentEntity;
use App\Entity\Teacher;
use App\Entity\Grade;
use App\Entity\Section;
use App\Entity\Subject;
use App\Entity\Enrollment;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create Admin User
        $admin = new User();
        $admin->setEmail('admin@oxford.edu.gt');
        $admin->setRoles(['ROLE_ADMIN', 'ROLE_ADMIN_SISTEMAS']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $admin->setFirstName('Administrador');
        $admin->setLastName('Sistema');
        $admin->setPhone('5555-1234');
        $admin->setIsActive(true);
        $manager->persist($admin);

        // Create Secretary User
        $secretary = new User();
        $secretary->setEmail('secretaria@oxford.edu.gt');
        $secretary->setRoles(['ROLE_SECRETARIA']);
        $secretary->setPassword($this->passwordHasher->hashPassword($secretary, 'secretaria123'));
        $secretary->setFirstName('María');
        $secretary->setLastName('González');
        $secretary->setPhone('5555-2345');
        $secretary->setIsActive(true);
        $manager->persist($secretary);

        // Create Coordinator User
        $coordinator = new User();
        $coordinator->setEmail('coordinador@oxford.edu.gt');
        $coordinator->setRoles(['ROLE_COORDINACION']);
        $coordinator->setPassword($this->passwordHasher->hashPassword($coordinator, 'coordinador123'));
        $coordinator->setFirstName('Carlos');
        $coordinator->setLastName('Ramírez');
        $coordinator->setPhone('5555-3456');
        $coordinator->setIsActive(true);
        $manager->persist($coordinator);

        // Create Teacher Users
        $teachers = [];
        $teacherData = [
            ['email' => 'teacher1@oxford.edu.gt', 'firstName' => 'Ana', 'lastName' => 'Martínez', 'specialization' => 'Matemáticas'],
            ['email' => 'teacher2@oxford.edu.gt', 'firstName' => 'Juan', 'lastName' => 'López', 'specialization' => 'Ciencias'],
            ['email' => 'teacher3@oxford.edu.gt', 'firstName' => 'Laura', 'lastName' => 'Pérez', 'specialization' => 'Inglés'],
        ];

        foreach ($teacherData as $data) {
            $user = new User();
            $user->setEmail($data['email']);
            $user->setRoles(['ROLE_MAESTRO']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'teacher123'));
            $user->setFirstName($data['firstName']);
            $user->setLastName($data['lastName']);
            $user->setPhone('5555-' . rand(4000, 9999));
            $user->setIsActive(true);
            $manager->persist($user);

            $teacher = new Teacher();
            $teacher->setUser($user);
            $teacher->setSpecialization($data['specialization']);
            $teacher->setHireDate(new \DateTime('-' . rand(1, 5) . ' years'));
            $teacher->setBirthDate(new \DateTime('-' . rand(25, 45) . ' years'));
            $manager->persist($teacher);
            $teachers[] = $teacher;
        }

        // Create Grades
        $grades = [];
        $gradeData = [
            ['name' => 'Primero Primaria', 'level' => 'Primaria'],
            ['name' => 'Segundo Primaria', 'level' => 'Primaria'],
            ['name' => 'Tercero Primaria', 'level' => 'Primaria'],
            ['name' => 'Primero Básico', 'level' => 'Básico'],
            ['name' => 'Segundo Básico', 'level' => 'Básico'],
        ];

        foreach ($gradeData as $data) {
            $grade = new Grade();
            $grade->setName($data['name']);
            $grade->setLevel($data['level']);
            $grade->setDescription('Grado ' . $data['name']);
            $manager->persist($grade);
            $grades[] = $grade;
        }

        // Create Sections
        $sections = [];
        foreach ($grades as $grade) {
            foreach (['A', 'B'] as $sectionName) {
                $section = new Section();
                $section->setGrade($grade);
                $section->setName($sectionName);
                $section->setCapacity(30);
                $section->setAcademicYear(2025);
                $manager->persist($section);
                $sections[] = $section;
            }
        }

        // Create Subjects
        $subjects = [];
        $subjectData = [
            ['name' => 'Matemáticas', 'code' => 'MAT'],
            ['name' => 'Comunicación y Lenguaje', 'code' => 'CL'],
            ['name' => 'Ciencias Naturales', 'code' => 'CN'],
            ['name' => 'Ciencias Sociales', 'code' => 'CS'],
            ['name' => 'Inglés', 'code' => 'ING'],
            ['name' => 'Educación Física', 'code' => 'EF'],
        ];

        foreach ($subjectData as $data) {
            $subject = new Subject();
            $subject->setName($data['name']);
            $subject->setCode($data['code']);
            $subject->setDescription('Curso de ' . $data['name']);
            $manager->persist($subject);
            $subjects[] = $subject;
        }

        // Create Parent and Student Users
        for ($i = 1; $i <= 10; $i++) {
            // Parent User
            $parentUser = new User();
            $parentUser->setEmail("padre{$i}@example.com");
            $parentUser->setRoles(['ROLE_PADRE_FAMILIA']);
            $parentUser->setPassword($this->passwordHasher->hashPassword($parentUser, 'padre123'));
            $parentUser->setFirstName("Padre{$i}");
            $parentUser->setLastName("Apellido{$i}");
            $parentUser->setPhone('5555-' . rand(5000, 9999));
            $parentUser->setIsActive(true);
            $manager->persist($parentUser);

            $parent = new ParentEntity();
            $parent->setUser($parentUser);
            $parent->setPersonalId('DPI-' . rand(1000000000, 9999999999));
            $parent->setProfession('Profesión ' . $i);
            $parent->setWorkplace('Empresa ' . $i);
            $manager->persist($parent);

            // Student User
            $studentUser = new User();
            $studentUser->setEmail("alumno{$i}@example.com");
            $studentUser->setRoles(['ROLE_ALUMNO']);
            $studentUser->setPassword($this->passwordHasher->hashPassword($studentUser, 'alumno123'));
            $studentUser->setFirstName("Alumno{$i}");
            $studentUser->setLastName("Apellido{$i}");
            $studentUser->setPhone('5555-' . rand(6000, 9999));
            $studentUser->setIsActive(true);
            $manager->persist($studentUser);

            $student = new Student();
            $student->setUser($studentUser);
            $student->setPersonalId('CUI-' . rand(1000000000, 9999999999));
            $student->setBirthDate(new \DateTime('-' . rand(6, 16) . ' years'));
            $student->setGender($i % 2 === 0 ? 'M' : 'F');
            $student->setNationality('Guatemalteca');
            $student->setAddress('Dirección ' . $i . ', Guatemala');
            $student->setEmergencyContact([
                'name' => 'Contacto Emergencia ' . $i,
                'phone' => '5555-' . rand(7000, 9999),
                'relationship' => 'Familiar'
            ]);
            $student->addParent($parent);
            $manager->persist($student);

            // Create Enrollment
            $section = $sections[array_rand($sections)];
            $enrollment = new Enrollment();
            $enrollment->setStudent($student);
            $enrollment->setGrade($section->getGrade());
            $enrollment->setSection($section);
            $enrollment->setAcademicYear(2025);
            $enrollment->setStatus('active');
            $enrollment->setEnrollmentDate(new \DateTime('-' . rand(1, 30) . ' days'));
            $manager->persist($enrollment);
        }

        $manager->flush();
    }
}
