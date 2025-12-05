<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Contract;
use App\Entity\Enrollment;
use App\Entity\Grade;
use App\Entity\Guardian;
use App\Entity\Student;
use App\Service\ContractService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

class ContractServiceTest extends TestCase
{
    private MockObject|EntityManagerInterface $entityManager;
    private MockObject|Environment $twig;
    private ContractService $service;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->twig = $this->createMock(Environment::class);

        $this->service = new ContractService($this->entityManager, $this->twig);
    }

    public function testGenerateContractCreatesValidContract(): void
    {
        $grade = $this->createMock(Grade::class);
        $grade->method('getName')->willReturn('Primero Primaria');

        $student = $this->createMock(Student::class);
        $student->method('getFullName')->willReturn('Juan Pérez');

        $enrollment = $this->createMock(Enrollment::class);
        $enrollment->method('getStudent')->willReturn($student);
        $enrollment->method('getGrade')->willReturn($grade);
        $enrollment->method('getAcademicYear')->willReturn(2024);

        $parent = $this->createMock(Guardian::class);
        $parent->method('getFullName')->willReturn('Pedro Pérez');
        $parent->method('getDui')->willReturn('12345678-9');

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $contract = $this->service->generateContract($enrollment, $parent, 5000.00, 12);

        $this->assertInstanceOf(Contract::class, $contract);
        $this->assertEquals(5000.00, $contract->getTotalAmount());
        $this->assertEquals('pending', $contract->getStatus());
    }

    public function testGenerateContractNumberIsUnique(): void
    {
        $grade = $this->createMock(Grade::class);
        $student = $this->createMock(Student::class);
        $enrollment = $this->createMock(Enrollment::class);
        $enrollment->method('getStudent')->willReturn($student);
        $enrollment->method('getGrade')->willReturn($grade);
        $enrollment->method('getAcademicYear')->willReturn(2024);

        $parent = $this->createMock(Guardian::class);
        $parent->method('getDui')->willReturn('12345678-9');

        $this->entityManager->method('persist');
        $this->entityManager->method('flush');

        $contract1 = $this->service->generateContract($enrollment, $parent, 1000.00);
        $contract2 = $this->service->generateContract($enrollment, $parent, 2000.00);

        $this->assertNotEquals($contract1->getContractNumber(), $contract2->getContractNumber());
    }

    public function testSignContractUpdatesStatus(): void
    {
        $contract = $this->createMock(Contract::class);
        $contract->expects($this->once())->method('setSignature')->with('signature-data');
        $contract->expects($this->once())->method('setSignedAt');
        $contract->expects($this->once())->method('setStatus')->with('signed');

        $this->entityManager->expects($this->once())->method('flush');

        $this->service->signContract($contract, 'signature-data');
    }
}
