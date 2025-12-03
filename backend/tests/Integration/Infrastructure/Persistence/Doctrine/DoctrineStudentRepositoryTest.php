<?php

declare(strict_types=1);

namespace App\Tests\Integration\Infrastructure\Persistence\Doctrine;

use App\Domain\Student\StudentRepositoryInterface;
use App\Domain\Student\ValueObject\Email;
use App\Domain\Student\ValueObject\PersonName;
use App\Domain\Student\ValueObject\StudentId;
use App\Entity\Student; // Usando la entidad de Doctrine por ahora para persistencia
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DoctrineStudentRepositoryTest extends KernelTestCase
{
    private ?StudentRepositoryInterface $repository;
    private $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->repository = $container->get(StudentRepositoryInterface::class);
        $this->entityManager = $container->get('doctrine')->getManager();

        // Limpiar base de datos de prueba (idealmente usar DAMADoctrineTestBundle)
        $this->entityManager->createQuery('DELETE FROM App\Entity\Student')->execute();
    }

    public function testCanSaveAndFindStudent(): void
    {
        // Crear un estudiante usando la entidad Doctrine (adaptador)
        // En una implementación pura DDD, usaríamos el modelo de dominio y un mapper
        $student = new Student();
        $student->setFirstName('Juan');
        $student->setLastName('Perez');
        $student->setEmail('juan.perez@school.com');
        $student->setCreatedAt(new \DateTime());

        $this->entityManager->persist($student);
        $this->entityManager->flush();

        $savedStudent = $this->repository->findById(new StudentId($student->getId()));

        $this->assertNotNull($savedStudent);
        $this->assertEquals('Juan', $savedStudent->getFirstName());
        $this->assertEquals('juan.perez@school.com', $savedStudent->getEmail());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
        $this->repository = null;
    }
}
