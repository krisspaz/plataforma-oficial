<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Enrollment\Command;

use App\Application\Enrollment\Command\CreateEnrollmentCommand;
use App\Application\Enrollment\Command\CreateEnrollmentHandler;
use App\Entity\Enrollment;
use App\Entity\Grade;
use App\Entity\Section;
use App\Entity\Student;
use App\Repository\EnrollmentRepository;
use App\Repository\SectionRepository;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateEnrollmentHandlerTest extends TestCase
{
    private MockObject|EnrollmentRepository $enrollmentRepository;
    private MockObject|StudentRepository $studentRepository;
    private MockObject|SectionRepository $sectionRepository;
    private MockObject|EntityManagerInterface $entityManager;
    private CreateEnrollmentHandler $handler;

    protected function setUp(): void
    {
        $this->enrollmentRepository = $this->createMock(EnrollmentRepository::class);
        $this->studentRepository = $this->createMock(StudentRepository::class);
        $this->sectionRepository = $this->createMock(SectionRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->handler = new CreateEnrollmentHandler(
            $this->enrollmentRepository,
            $this->studentRepository,
            $this->sectionRepository,
            $this->entityManager
        );
    }

    public function testInvokeCreatesEnrollmentSuccessfully(): void
    {
        $student = $this->createMock(Student::class);
        $grade = $this->createMock(Grade::class);
        $section = $this->createMock(Section::class);
        $section->method('hasAvailableSpace')->willReturn(true);
        $section->method('getGrade')->willReturn($grade);
        $section->method('getAcademicYear')->willReturn(2024);

        $this->studentRepository->method('find')->with(1)->willReturn($student);
        $this->sectionRepository->method('find')->with(1)->willReturn($section);
        $this->enrollmentRepository->method('findBy')->willReturn([]);

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $command = new CreateEnrollmentCommand(studentId: 1, sectionId: 1);
        $result = ($this->handler)($command);

        $this->assertArrayHasKey('enrollment', $result);
        $this->assertEquals(201, $result['code']);
    }

    public function testInvokeReturns404WhenStudentNotFound(): void
    {
        $this->studentRepository->method('find')->with(999)->willReturn(null);

        $command = new CreateEnrollmentCommand(studentId: 999, sectionId: 1);
        $result = ($this->handler)($command);

        $this->assertEquals('Student not found', $result['error']);
        $this->assertEquals(404, $result['code']);
    }

    public function testInvokeReturns404WhenSectionNotFound(): void
    {
        $student = $this->createMock(Student::class);
        $this->studentRepository->method('find')->willReturn($student);
        $this->sectionRepository->method('find')->with(999)->willReturn(null);

        $command = new CreateEnrollmentCommand(studentId: 1, sectionId: 999);
        $result = ($this->handler)($command);

        $this->assertEquals('Section not found', $result['error']);
        $this->assertEquals(404, $result['code']);
    }

    public function testInvokeReturns400WhenSectionIsFull(): void
    {
        $student = $this->createMock(Student::class);
        $section = $this->createMock(Section::class);
        $section->method('hasAvailableSpace')->willReturn(false);
        $section->method('getCapacity')->willReturn(30);
        $section->method('getCurrentEnrollmentCount')->willReturn(30);

        $this->studentRepository->method('find')->willReturn($student);
        $this->sectionRepository->method('find')->willReturn($section);

        $command = new CreateEnrollmentCommand(studentId: 1, sectionId: 1);
        $result = ($this->handler)($command);

        $this->assertEquals('Section is full', $result['error']);
        $this->assertEquals(400, $result['code']);
    }

    public function testInvokeReturns400WhenStudentAlreadyEnrolled(): void
    {
        $student = $this->createMock(Student::class);
        $section = $this->createMock(Section::class);
        $section->method('hasAvailableSpace')->willReturn(true);
        $section->method('getAcademicYear')->willReturn(2024);

        $existingEnrollment = $this->createMock(Enrollment::class);

        $this->studentRepository->method('find')->willReturn($student);
        $this->sectionRepository->method('find')->willReturn($section);
        $this->enrollmentRepository->method('findBy')->willReturn([$existingEnrollment]);

        $command = new CreateEnrollmentCommand(studentId: 1, sectionId: 1);
        $result = ($this->handler)($command);

        $this->assertEquals('Student is already enrolled for this academic year', $result['error']);
        $this->assertEquals(400, $result['code']);
    }
}
