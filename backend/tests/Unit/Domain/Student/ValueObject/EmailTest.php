<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Student\ValueObject;

use App\Domain\Student\ValueObject\Email;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function testCanCreateValidEmail(): void
    {
        $email = new Email('test@example.com');
        $this->assertEquals('test@example.com', $email->getValue());
    }

    public function testCannotCreateInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Email('invalid-email');
    }

    public function testNormalization(): void
    {
        $email = new Email('  Test@Example.COM  ');
        $this->assertEquals('test@example.com', $email->getValue());
    }
}
