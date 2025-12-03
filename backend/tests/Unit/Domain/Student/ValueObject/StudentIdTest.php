<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Student\ValueObject;

use App\Domain\Student\ValueObject\StudentId;
use PHPUnit\Framework\TestCase;

class StudentIdTest extends TestCase
{
    public function testCanCreateValidStudentId(): void
    {
        $id = new StudentId(123);
        $this->assertEquals(123, $id->getValue());
        $this->assertEquals('123', (string) $id);
    }

    public function testCannotCreateInvalidStudentId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new StudentId(-1);
    }

    public function testEquality(): void
    {
        $id1 = new StudentId(100);
        $id2 = new StudentId(100);
        $id3 = new StudentId(200);

        $this->assertTrue($id1->equals($id2));
        $this->assertFalse($id1->equals($id3));
    }
}
