<?php

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\PaymentRepository;

class PaymentControllerTest extends WebTestCase
{
    public function testGetPaymentsUnauthorized(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/payments');

        // Should be 401 because it's protected, or 200 if public.
        // Based on security.yaml, /api is authenticated.
        // However, we haven't set up a user token here.
        // Let's assume it returns 401.
        $this->assertResponseStatusCodeSame(401);
    }

    // We need a way to authenticate for functional tests.
    // Usually we create a user and get a token.
    // For now, let's just check that the endpoint exists and is protected.
}
