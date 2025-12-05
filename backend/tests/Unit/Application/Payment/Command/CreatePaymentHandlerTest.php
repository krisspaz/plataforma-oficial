<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Payment\Command;

use App\Application\Payment\Command\CreatePaymentCommand;
use App\Application\Payment\Command\CreatePaymentHandler;
use App\Entity\Enrollment;
use App\Entity\Payment;
use App\Repository\EnrollmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CreatePaymentHandlerTest extends TestCase
{
    private MockObject|EnrollmentRepository $enrollmentRepository;
    private MockObject|EntityManagerInterface $entityManager;
    private MockObject|LoggerInterface $logger;
    private CreatePaymentHandler $handler;

    protected function setUp(): void
    {
        $this->enrollmentRepository = $this->createMock(EnrollmentRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new CreatePaymentHandler(
            $this->enrollmentRepository,
            $this->entityManager,
            $this->logger
        );
    }

    public function testInvokeCreatesPaymentSuccessfully(): void
    {
        $enrollment = $this->createMock(Enrollment::class);
        $enrollment->method('getId')->willReturn(1);

        $this->enrollmentRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($enrollment);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Payment::class));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Payment created', $this->anything());

        $command = new CreatePaymentCommand(
            enrollmentId: 1,
            amount: 150.00,
            paymentType: 'mensualidad',
            paymentMethod: 'efectivo'
        );

        $result = ($this->handler)($command);

        $this->assertInstanceOf(Payment::class, $result);
    }

    public function testInvokeReturnsNullWhenEnrollmentNotFound(): void
    {
        $this->enrollmentRepository
            ->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->never())->method('flush');

        $command = new CreatePaymentCommand(
            enrollmentId: 999,
            amount: 100.00
        );

        $result = ($this->handler)($command);

        $this->assertNull($result);
    }

    public function testInvokeThrowsExceptionForInvalidDateFormat(): void
    {
        $enrollment = $this->createMock(Enrollment::class);
        $enrollment->method('getId')->willReturn(1);

        $this->enrollmentRepository
            ->method('find')
            ->willReturn($enrollment);

        $command = new CreatePaymentCommand(
            enrollmentId: 1,
            amount: 100.00,
            dueDate: 'invalid-date'
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid date format');

        ($this->handler)($command);
    }
}
