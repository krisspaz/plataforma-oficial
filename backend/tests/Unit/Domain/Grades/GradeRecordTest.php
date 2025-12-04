<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Grades;

use App\Domain\Grades\Entity\GradeRecord;
use App\Entity\Student;
use App\Entity\Subject;
use App\Entity\Teacher;
use PHPUnit\Framework\TestCase;

class GradeRecordTest extends TestCase
{
    private Student $student;
    private Subject $subject;
    private Teacher $teacher;

    protected function setUp(): void
    {
        $this->student = $this->createMock(Student::class);
        $this->subject = $this->createMock(Subject::class);
        $this->teacher = $this->createMock(Teacher::class);
    }

    public function testCreateGradeRecord(): void
    {
        $record = new GradeRecord(
            $this->student,
            $this->subject,
            $this->teacher,
            1,
            2025,
            85.5
        );

        $this->assertNotNull($record->getId());
        $this->assertSame($this->student, $record->getStudent());
        $this->assertSame($this->subject, $record->getSubject());
        $this->assertSame($this->teacher, $record->getRecordedBy());
        $this->assertSame(1, $record->getBimester());
        $this->assertSame(2025, $record->getAcademicYear());
        $this->assertSame(85.5, $record->getGrade());
    }

    public function testPassingGrade(): void
    {
        $record = new GradeRecord($this->student, $this->subject, $this->teacher, 1, 2025, 60.0);
        $this->assertTrue($record->isPassing());

        $record2 = new GradeRecord($this->student, $this->subject, $this->teacher, 1, 2025, 59.9);
        $this->assertFalse($record2->isPassing());
    }

    public function testLetterGrades(): void
    {
        $testCases = [
            [95, 'A'],
            [85, 'B'],
            [75, 'C'],
            [65, 'D'],
            [50, 'F'],
        ];

        foreach ($testCases as [$grade, $expected]) {
            $record = new GradeRecord($this->student, $this->subject, $this->teacher, 1, 2025, $grade);
            $this->assertSame($expected, $record->getLetterGrade(), "Grade $grade should be $expected");
        }
    }

    public function testInvalidBimester(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GradeRecord($this->student, $this->subject, $this->teacher, 5, 2025, 80);
    }

    public function testBimesterZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GradeRecord($this->student, $this->subject, $this->teacher, 0, 2025, 80);
    }

    public function testGradeTooHigh(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GradeRecord($this->student, $this->subject, $this->teacher, 1, 2025, 105);
    }

    public function testNegativeGrade(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new GradeRecord($this->student, $this->subject, $this->teacher, 1, 2025, -5);
    }

    public function testUpdateGrade(): void
    {
        $record = new GradeRecord($this->student, $this->subject, $this->teacher, 1, 2025, 70);

        $newTeacher = $this->createMock(Teacher::class);
        $record->updateGrade(85, $newTeacher, 'Updated after review');

        $this->assertSame(85.0, $record->getGrade());
        $this->assertSame($newTeacher, $record->getRecordedBy());
        $this->assertSame('Updated after review', $record->getComments());
        $this->assertNotNull($record->getUpdatedAt());
    }

    public function testBimesterName(): void
    {
        $record = new GradeRecord($this->student, $this->subject, $this->teacher, 3, 2025, 80);
        $this->assertSame('Tercer Bimestre', $record->getBimesterName());
    }
}
