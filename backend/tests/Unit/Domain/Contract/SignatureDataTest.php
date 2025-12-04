<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Contract;

use App\Domain\Contract\ValueObject\SignatureData;
use PHPUnit\Framework\TestCase;

class SignatureDataTest extends TestCase
{
    public function testCreateValidSignature(): void
    {
        $imageData = 'data:image/png;base64,' . base64_encode('test image data');
        $ipAddress = '192.168.1.1';

        $signature = new SignatureData($imageData, $ipAddress);

        $this->assertSame($imageData, $signature->getImageData());
        $this->assertSame($ipAddress, $signature->getIpAddress());
        $this->assertNotNull($signature->getSignedAt());
        $this->assertNotEmpty($signature->getHash());
    }

    public function testEmptyImageDataThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SignatureData('', '192.168.1.1');
    }

    public function testInvalidIpAddressThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $imageData = 'data:image/png;base64,' . base64_encode('test');
        new SignatureData($imageData, 'invalid-ip');
    }

    public function testHashIsConsistent(): void
    {
        $imageData = 'data:image/png;base64,' . base64_encode('test image');
        $ip = '10.0.0.1';

        $sig1 = new SignatureData($imageData, $ip);
        $sig2 = new SignatureData($imageData, $ip);

        // Hashes should be different due to timestamp
        $this->assertNotSame($sig1->getHash(), $sig2->getHash());
    }

    public function testToArray(): void
    {
        $imageData = 'data:image/png;base64,' . base64_encode('test');
        $signature = new SignatureData($imageData, '127.0.0.1');

        $array = $signature->toArray();

        $this->assertArrayHasKey('image_data', $array);
        $this->assertArrayHasKey('ip_address', $array);
        $this->assertArrayHasKey('signed_at', $array);
        $this->assertArrayHasKey('hash', $array);
    }
}
