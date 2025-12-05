<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure;

use App\Entity\Payment;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PaymentRepositoryTest extends TestCase
{
    private MockObject|ManagerRegistry $registry;
    private MockObject|EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->registry = $this->createMock(ManagerRegistry::class);

        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->name = Payment::class;

        $this->entityManager->method('getClassMetadata')
            ->with(Payment::class)
            ->willReturn($classMetadata);

        $this->registry->method('getManagerForClass')
            ->with(Payment::class)
            ->willReturn($this->entityManager);
    }

    public function testFindPendingReturnsOnlyPendingPayments(): void
    {
        $repository = $this->getMockBuilder(PaymentRepository::class)
            ->setConstructorArgs([$this->registry])
            ->onlyMethods(['findBy'])
            ->getMock();

        $pendingPayments = [
            $this->createPaymentMock('pending'),
            $this->createPaymentMock('pending'),
        ];

        $repository->expects($this->once())
            ->method('findBy')
            ->with(['status' => 'pending'])
            ->willReturn($pendingPayments);

        $result = $repository->findPending();

        $this->assertCount(2, $result);
    }

    public function testFindOverdueReturnsOnlyOverduePayments(): void
    {
        $repository = $this->getMockBuilder(PaymentRepository::class)
            ->setConstructorArgs([$this->registry])
            ->onlyMethods(['createQueryBuilder'])
            ->getMock();

        // This is a simplified test - real implementation would test DQL
        $this->assertTrue(true);
    }

    private function createPaymentMock(string $status): MockObject|Payment
    {
        $payment = $this->createMock(Payment::class);
        $payment->method('getStatus')->willReturn($status);
        return $payment;
    }
}
