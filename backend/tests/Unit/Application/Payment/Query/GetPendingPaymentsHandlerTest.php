<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Payment\Query;

use App\Application\Payment\Query\GetPendingPaymentsHandler;
use App\Application\Payment\Query\GetPendingPaymentsQuery;
use App\Repository\PaymentRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetPendingPaymentsHandlerTest extends TestCase
{
    private MockObject|PaymentRepository $paymentRepository;
    private GetPendingPaymentsHandler $handler;

    protected function setUp(): void
    {
        $this->paymentRepository = $this->createMock(PaymentRepository::class);
        $this->handler = new GetPendingPaymentsHandler($this->paymentRepository);
    }

    public function testInvokeReturnsPendingPayments(): void
    {
        $pendingPayments = [
            ['id' => 1, 'amount' => 100, 'status' => 'pending'],
            ['id' => 2, 'amount' => 200, 'status' => 'pending'],
        ];

        $this->paymentRepository
            ->expects($this->once())
            ->method('findPending')
            ->willReturn($pendingPayments);

        $query = new GetPendingPaymentsQuery();
        $result = ($this->handler)($query);

        $this->assertCount(2, $result);
        $this->assertEquals(100, $result[0]['amount']);
        $this->assertEquals(200, $result[1]['amount']);
    }

    public function testInvokeReturnsEmptyArrayWhenNoPendingPayments(): void
    {
        $this->paymentRepository
            ->expects($this->once())
            ->method('findPending')
            ->willReturn([]);

        $query = new GetPendingPaymentsQuery();
        $result = ($this->handler)($query);

        $this->assertEmpty($result);
    }
}
