<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ScheduleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ScheduleRepository::class)]
#[ORM\Table(name: 'schedules')]
#[ApiResource(
    normalizationContext: ['groups' => ['schedule:read']],
    denormalizationContext: ['groups' => ['schedule:write']]
)]
class Schedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['schedule:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['schedule:read', 'schedule:write'])]
    private ?Section $section = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['schedule:read', 'schedule:write'])]
    private ?Subject $subject = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['schedule:read', 'schedule:write'])]
    private ?Teacher $teacher = null;

    #[ORM\Column]
    #[Groups(['schedule:read', 'schedule:write'])]
    private ?int $dayOfWeek = null; // 1=Monday, 7=Sunday

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Groups(['schedule:read', 'schedule:write'])]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Groups(['schedule:read', 'schedule:write'])]
    private ?\DateTimeInterface $endTime = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['schedule:read', 'schedule:write'])]
    private ?string $classroom = null;

    #[ORM\Column]
    #[Groups(['schedule:read', 'schedule:write'])]
    private ?int $academicYear = null;

    public function __construct()
    {
        $this->academicYear = (int) date('Y');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSection(): ?Section
    {
        return $this->section;
    }

    public function setSection(?Section $section): static
    {
        $this->section = $section;
        return $this;
    }

    public function getSubject(): ?Subject
    {
        return $this->subject;
    }

    public function setSubject(?Subject $subject): static
    {
        $this->subject = $subject;
        return $this;
    }

    public function getTeacher(): ?Teacher
    {
        return $this->teacher;
    }

    public function setTeacher(?Teacher $teacher): static
    {
        $this->teacher = $teacher;
        return $this;
    }

    public function getDayOfWeek(): ?int
    {
        return $this->dayOfWeek;
    }

    public function setDayOfWeek(int $dayOfWeek): static
    {
        $this->dayOfWeek = $dayOfWeek;
        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): static
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeInterface $endTime): static
    {
        $this->endTime = $endTime;
        return $this;
    }

    public function getClassroom(): ?string
    {
        return $this->classroom;
    }

    public function setClassroom(?string $classroom): static
    {
        $this->classroom = $classroom;
        return $this;
    }

    public function getAcademicYear(): ?int
    {
        return $this->academicYear;
    }

    public function setAcademicYear(int $academicYear): static
    {
        $this->academicYear = $academicYear;
        return $this;
    }

    public function getDayName(): string
    {
        $days = ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        return $days[$this->dayOfWeek] ?? '';
    }

    public function getDuration(): ?\DateInterval
    {
        if (!$this->startTime || !$this->endTime) {
            return null;
        }
        
        return $this->startTime->diff($this->endTime);
    }
}
