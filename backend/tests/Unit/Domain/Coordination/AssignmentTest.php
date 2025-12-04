<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Coordination;

use App\Domain\Coordination\Entity\Assignment;
use App\Entity\Grade;
use App\Entity\Section;
use App\Entity\Subject;
use App\Entity\Teacher;
use PHPUnit\Framework\TestCase;

class AssignmentTest extends TestCase
{
    public function testCreateAssignment(): void
    {
        $teacher = $this->createMock(Teacher::class);
        $subject = $this->createMock(Subject::class);
        $grade = $this->createMock(Grade::class);
        $section = $this->createMock(Section::class);

        $assignment = new Assignment($teacher, $subject, $grade, $section, 2025);

        $this->assertNotNull($assignment->getId());
        $this->assertSame($teacher, $assignment->getTeacher());
        $this->assertSame($subject, $assignment->getSubject());
        $this->assertSame($grade, $assignment->getGrade());
        $this->assertSame($section, $assignment->getSection());
        $this->assertSame(2025, $assignment->getAcademicYear());
    }

    public function testDefaultAcademicYear(): void
    {
        $teacher = $this->createMock(Teacher::class);
        $subject = $this->createMock(Subject::class);
        $grade = $this->createMock(Grade::class);
        $section = $this->createMock(Section::class);

        $assignment = new Assignment($teacher, $subject, $grade, $section);

        $this->assertSame((int) date('Y'), $assignment->getAcademicYear());
    }
}
