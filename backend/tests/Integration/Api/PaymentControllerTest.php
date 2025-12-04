<?php

declare(strict_types=1);

namespace App\Tests\Integration\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PaymentControllerTest extends WebTestCase
{
    public function testCreatePaymentPlanRequiresAuth(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/payments/plans', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'enrollment_id' => 1,
            'total_amount' => 1500,
            'num_installments' => 10
        ]));

        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetDebtorsRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/payments/debtors');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetDailyClosureRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/payments/daily-closure');

        $this->assertResponseStatusCodeSame(401);
    }
}
