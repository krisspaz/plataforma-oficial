<?php

declare(strict_types=1);

namespace App\Domain\Grades\Entity;

use App\Entity\Student;
use App\Entity\Subject;
use App\Entity\Teacher;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'grade_records')]
#[ORM\UniqueConstraint(name: 'unique_grade_record', columns: ['student_id', 'subject_id', 'bimester', 'academic_year'])]
class GradeRecord
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: Student::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Student $student;

    #[ORM\ManyToOne(targetEntity: Subject::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Subject $subject;

    #[ORM\ManyToOne(targetEntity: Teacher::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Teacher $recordedBy;

    #[ORM\Column(type: 'integer')]
    private int $bimester; // 1-4

    #[ORM\Column(type: 'integer')]
    private int $academicYear;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2)]
    private float $grade;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $comments = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $recordedAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $updatedAt = null;

    private const MIN_PASSING_GRADE = 60.0;
    private const MAX_GRADE = 100.0;

    public function __construct(
        Student $student,
        Subject $subject,
        Teacher $recordedBy,
        int $bimester,
        int $academicYear,
        float $grade,
        ?string $comments = null
    ) {
        if ($bimester < 1 || $bimester > 4) {
            throw new \InvalidArgumentException('Bimester must be between 1 and 4');
        }

        if ($grade < 0 || $grade > self::MAX_GRADE) {
            throw new \InvalidArgumentException('Grade must be between 0 and 100');
        }

        $this->id = Uuid::v7();
        $this->student = $student;
        $this->subject = $subject;
        $this->recordedBy = $recordedBy;
        $this->bimester = $bimester;
        $this->academicYear = $academicYear;
        $this->grade = round($grade, 2);
        $this->comments = $comments;
        $this->recordedAt = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getStudent(): Student
    {
        return $this->student;
    }

    public function getSubject(): Subject
    {
        return $this->subject;
    }

    public function getRecordedBy(): Teacher
    {
        return $this->recordedBy;
    }

    public function getBimester(): int
    {
        return $this->bimester;
    }

    public function getAcademicYear(): int
    {
        return $this->academicYear;
    }

    public function getGrade(): float
    {
        return $this->grade;
    }

    public function getComments(): ?string
    {
        return $this->comments;
    }

    public function getRecordedAt(): DateTimeImmutable
    {
        return $this->recordedAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isPassing(): bool
    {
        return $this->grade >= self::MIN_PASSING_GRADE;
    }

    public function getLetterGrade(): string
    {
        if ($this->grade >= 90) return 'A';
        if ($this->grade >= 80) return 'B';
        if ($this->grade >= 70) return 'C';
        if ($this->grade >= 60) return 'D';
        return 'F';
    }

    public function updateGrade(float $newGrade, Teacher $updatedBy, ?string $comments = null): void
    {
        if ($newGrade < 0 || $newGrade > self::MAX_GRADE) {
            throw new \InvalidArgumentException('Grade must be between 0 and 100');
        }

        $this->grade = round($newGrade, 2);
        $this->recordedBy = $updatedBy;
        if ($comments !== null) {
            $this->comments = $comments;
        }
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getBimesterName(): string
    {
        $names = [
            1 => 'Primer Bimestre',
            2 => 'Segundo Bimestre',
            3 => 'Tercer Bimestre',
            4 => 'Cuarto Bimestre',
        ];
        return $names[$this->bimester] ?? 'Bimestre ' . $this->bimester;
    }
}
