<?php

declare(strict_types=1);

namespace App\Domain\Grades\Entity;

use App\Entity\Grade;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'bimester_closures')]
#[ORM\UniqueConstraint(name: 'unique_closure', columns: ['grade_id', 'bimester', 'academic_year'])]
class BimesterClosure
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: Grade::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Grade $grade;

    #[ORM\Column(type: 'integer')]
    private int $bimester;

    #[ORM\Column(type: 'integer')]
    private int $academicYear;

    #[ORM\Column(type: 'boolean')]
    private bool $isClosed = false;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $closedBy = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $closedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $reopenedBy = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $reopenedAt = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $reopenReason = null;

    public function __construct(Grade $grade, int $bimester, int $academicYear)
    {
        if ($bimester < 1 || $bimester > 4) {
            throw new \InvalidArgumentException('Bimester must be between 1 and 4');
        }

        $this->id = Uuid::v7();
        $this->grade = $grade;
        $this->bimester = $bimester;
        $this->academicYear = $academicYear;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getGrade(): Grade
    {
        return $this->grade;
    }

    public function getBimester(): int
    {
        return $this->bimester;
    }

    public function getAcademicYear(): int
    {
        return $this->academicYear;
    }

    public function isClosed(): bool
    {
        return $this->isClosed;
    }

    public function getClosedBy(): ?User
    {
        return $this->closedBy;
    }

    public function getClosedAt(): ?DateTimeImmutable
    {
        return $this->closedAt;
    }

    public function close(User $user): void
    {
        if ($this->isClosed) {
            throw new \DomainException('Bimester is already closed');
        }

        $this->isClosed = true;
        $this->closedBy = $user;
        $this->closedAt = new DateTimeImmutable();
    }

    public function reopen(User $user, string $reason): void
    {
        if (!$this->isClosed) {
            throw new \DomainException('Bimester is not closed');
        }

        $this->isClosed = false;
        $this->reopenedBy = $user;
        $this->reopenedAt = new DateTimeImmutable();
        $this->reopenReason = $reason;
    }

    public function getReopenedBy(): ?User
    {
        return $this->reopenedBy;
    }

    public function getReopenedAt(): ?DateTimeImmutable
    {
        return $this->reopenedAt;
    }

    public function getReopenReason(): ?string
    {
        return $this->reopenReason;
    }
}
