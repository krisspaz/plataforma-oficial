<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Payment\Query;

use App\Application\Payment\Query\GetPaymentsHandler;
use App\Application\Payment\Query\GetPaymentsQuery;
use App\Repository\PaymentRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetPaymentsHandlerTest extends TestCase
{
    private MockObject|PaymentRepository $paymentRepository;
    private GetPaymentsHandler $handler;

    protected function setUp(): void
    {
        $this->paymentRepository = $this->createMock(PaymentRepository::class);
        $this->handler = new GetPaymentsHandler($this->paymentRepository);
    }

    public function testInvokeWithoutStatusFilter(): void
    {
        $payments = [
            ['id' => 1, 'amount' => 100],
            ['id' => 2, 'amount' => 200],
        ];

        $this->paymentRepository
            ->expects($this->once())
            ->method('findBy')
            ->with([], ['createdAt' => 'DESC'], 20, 0)
            ->willReturn($payments);

        $this->paymentRepository
            ->expects($this->once())
            ->method('count')
            ->with([])
            ->willReturn(2);

        $query = new GetPaymentsQuery();
        $result = ($this->handler)($query);

        $this->assertCount(2, $result['payments']);
        $this->assertEquals(2, $result['total']);
        $this->assertEquals(1, $result['page']);
        $this->assertEquals(20, $result['limit']);
    }

    public function testInvokeWithStatusFilter(): void
    {
        $payments = [['id' => 1, 'amount' => 100, 'status' => 'pending']];

        $this->paymentRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['status' => 'pending'], ['createdAt' => 'DESC'], 20, 0)
            ->willReturn($payments);

        $this->paymentRepository
            ->expects($this->once())
            ->method('count')
            ->with(['status' => 'pending'])
            ->willReturn(1);

        $query = new GetPaymentsQuery(status: 'pending');
        $result = ($this->handler)($query);

        $this->assertCount(1, $result['payments']);
        $this->assertEquals(1, $result['total']);
    }

    public function testInvokeWithPagination(): void
    {
        $this->paymentRepository
            ->expects($this->once())
            ->method('findBy')
            ->with([], ['createdAt' => 'DESC'], 10, 20)
            ->willReturn([]);

        $this->paymentRepository
            ->expects($this->once())
            ->method('count')
            ->willReturn(50);

        $query = new GetPaymentsQuery(page: 3, limit: 10);
        $result = ($this->handler)($query);

        $this->assertEquals(3, $result['page']);
        $this->assertEquals(10, $result['limit']);
        $this->assertEquals(50, $result['total']);
    }

    public function testInvokeWithLimitExceedsMax(): void
    {
        $this->paymentRepository
            ->expects($this->once())
            ->method('findBy')
            ->with([], ['createdAt' => 'DESC'], 100, 0) // Max is 100
            ->willReturn([]);

        $this->paymentRepository->method('count')->willReturn(0);

        $query = new GetPaymentsQuery(limit: 500);
        $result = ($this->handler)($query);

        $this->assertEquals(100, $result['limit']);
    }
}
