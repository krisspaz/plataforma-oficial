<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Contract\Command;

use App\Application\Contract\Command\CreateContractCommand;
use App\Application\Contract\Command\CreateContractHandler;
use App\Entity\Contract;
use App\Entity\Enrollment;
use App\Entity\Guardian;
use App\Entity\User;
use App\Repository\EnrollmentRepository;
use App\Repository\ParentRepository;
use App\Service\ContractService;
use App\Service\NotificationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CreateContractHandlerTest extends TestCase
{
    private MockObject|EnrollmentRepository $enrollmentRepository;
    private MockObject|ParentRepository $parentRepository;
    private MockObject|ContractService $contractService;
    private MockObject|NotificationService $notificationService;
    private CreateContractHandler $handler;

    protected function setUp(): void
    {
        $this->enrollmentRepository = $this->createMock(EnrollmentRepository::class);
        $this->parentRepository = $this->createMock(ParentRepository::class);
        $this->contractService = $this->createMock(ContractService::class);
        $this->notificationService = $this->createMock(NotificationService::class);

        $this->handler = new CreateContractHandler(
            $this->enrollmentRepository,
            $this->parentRepository,
            $this->contractService,
            $this->notificationService
        );
    }

    public function testInvokeCreatesContractSuccessfully(): void
    {
        $enrollment = $this->createMock(Enrollment::class);
        $user = $this->createMock(User::class);
        $parent = $this->createMock(Guardian::class);
        $parent->method('getUser')->willReturn($user);

        $contract = $this->createMock(Contract::class);
        $contract->method('getId')->willReturn(1);
        $contract->method('getContractNumber')->willReturn('CNT-001');
        $contract->method('getParent')->willReturn($parent);

        $this->enrollmentRepository->method('find')->with(1)->willReturn($enrollment);
        $this->parentRepository->method('find')->with(1)->willReturn($parent);
        $this->contractService
            ->method('generateContract')
            ->with($enrollment, $parent, 5000.00, 12)
            ->willReturn($contract);

        $this->notificationService
            ->expects($this->once())
            ->method('createNotification');

        $command = new CreateContractCommand(
            enrollmentId: 1,
            parentId: 1,
            totalAmount: 5000.00,
            installments: 12
        );

        $result = ($this->handler)($command);

        $this->assertArrayHasKey('contract', $result);
        $this->assertEquals(201, $result['code']);
    }

    public function testInvokeReturns404WhenEnrollmentNotFound(): void
    {
        $this->enrollmentRepository->method('find')->with(999)->willReturn(null);

        $command = new CreateContractCommand(
            enrollmentId: 999,
            parentId: 1,
            totalAmount: 5000.00
        );

        $result = ($this->handler)($command);

        $this->assertEquals('Enrollment not found', $result['error']);
        $this->assertEquals(404, $result['code']);
    }

    public function testInvokeReturns404WhenParentNotFound(): void
    {
        $enrollment = $this->createMock(Enrollment::class);
        $this->enrollmentRepository->method('find')->willReturn($enrollment);
        $this->parentRepository->method('find')->with(999)->willReturn(null);

        $command = new CreateContractCommand(
            enrollmentId: 1,
            parentId: 999,
            totalAmount: 5000.00
        );

        $result = ($this->handler)($command);

        $this->assertEquals('Parent not found', $result['error']);
        $this->assertEquals(404, $result['code']);
    }
}
