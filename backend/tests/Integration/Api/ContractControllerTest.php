<?php

declare(strict_types=1);

namespace App\Tests\Integration\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContractControllerTest extends WebTestCase
{
    public function testGenerateContractRequiresAuth(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/contracts/generate', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'enrollment_id' => 1,
            'total_amount' => 1500,
            'num_installments' => 10
        ]));

        $this->assertResponseStatusCodeSame(401);
    }

    public function testSignContractRequiresAuth(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/contracts/1/sign', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'signature_data' => 'data:image/png;base64,test'
        ]));

        $this->assertResponseStatusCodeSame(401);
    }

    public function testDownloadContractRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/contracts/1/download');

        $this->assertResponseStatusCodeSame(401);
    }
}
