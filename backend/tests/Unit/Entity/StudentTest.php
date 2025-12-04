<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Student;
use PHPUnit\Framework\TestCase;

class StudentTest extends TestCase
{
    public function testCreateStudent(): void
    {
        $student = new Student();
        $student->setFirstName('John');
        $student->setLastName('Doe');
        $student->setEmail('john.doe@example.com');
        $student->setStatus('active');

        $this->assertEquals('John', $student->getFirstName());
        $this->assertEquals('Doe', $student->getLastName());
        $this->assertEquals('john.doe@example.com', $student->getEmail());
        $this->assertEquals('active', $student->getStatus());
        $this->assertInstanceOf(\DateTimeInterface::class, $student->getCreatedAt());
    }

    public function testStudentStatusDefaultsToActive(): void
    {
        $student = new Student();
        $this->assertEquals('active', $student->getStatus());
    }

    public function testSetAndGetBirthDate(): void
    {
        $student = new Student();
        $date = new \DateTime('2010-01-01');
        $student->setBirthDate($date);

        $this->assertEquals($date, $student->getBirthDate());
    }
}
