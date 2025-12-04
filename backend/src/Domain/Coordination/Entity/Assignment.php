<?php

declare(strict_types=1);

namespace App\Domain\Coordination\Entity;

use App\Entity\Grade;
use App\Entity\Section;
use App\Entity\Subject;
use App\Entity\Teacher;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'assignments')]
class Assignment
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: Teacher::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Teacher $teacher;

    #[ORM\ManyToOne(targetEntity: Subject::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Subject $subject;

    #[ORM\ManyToOne(targetEntity: Grade::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Grade $grade;

    #[ORM\ManyToOne(targetEntity: Section::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Section $section;

    #[ORM\Column(type: 'integer')]
    private int $academicYear;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    public function __construct(
        Teacher $teacher,
        Subject $subject,
        Grade $grade,
        Section $section,
        int $academicYear
    ) {
        $this->id = Uuid::v7();
        $this->teacher = $teacher;
        $this->subject = $subject;
        $this->grade = $grade;
        $this->section = $section;
        $this->academicYear = $academicYear;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getTeacher(): Teacher
    {
        return $this->teacher;
    }

    public function getSubject(): Subject
    {
        return $this->subject;
    }

    public function getGrade(): Grade
    {
        return $this->grade;
    }

    public function getSection(): Section
    {
        return $this->section;
    }

    public function getAcademicYear(): int
    {
        return $this->academicYear;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
