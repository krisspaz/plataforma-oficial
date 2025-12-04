<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Student\ValueObject;

use App\Domain\Student\ValueObject\StudentId;
use PHPUnit\Framework\TestCase;

class StudentIdTest extends TestCase
{
    public function testCanCreateValidStudentId(): void
    {
        $id = StudentId::fromInt(1);
        $this->assertEquals(1, $id->toInt());
    }

    public function testCannotCreateInvalidStudentId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        StudentId::fromInt(0);
    }

    public function testEquality(): void
    {
        $id1 = StudentId::fromInt(1);
        $id2 = StudentId::fromInt(1);
        $id3 = StudentId::fromInt(2);

        $this->assertEquals($id1, $id2); // Objects are not identical but equal in value if properties match
        $this->assertNotEquals($id1, $id3);
    }
}
