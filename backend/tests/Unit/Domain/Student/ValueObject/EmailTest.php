<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Student\ValueObject;

use App\Domain\Student\ValueObject\Email;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function testCanCreateValidEmail(): void
    {
        $email = Email::fromString('test@example.com');
        $this->assertEquals('test@example.com', (string) $email);
    }

    public function testCannotCreateInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Email::fromString('invalid-email');
    }

    public function testNormalization(): void
    {
        $email = Email::fromString('  Test@Example.COM  ');
        $this->assertEquals('test@example.com', (string) $email);
    }
}
