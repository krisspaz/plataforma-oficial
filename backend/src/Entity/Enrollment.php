<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\EnrollmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EnrollmentRepository::class)]
#[ORM\Table(name: 'enrollments')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['enrollment:read']],
    denormalizationContext: ['groups' => ['enrollment:write']]
)]
class Enrollment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['enrollment:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'enrollments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['enrollment:read', 'enrollment:write'])]
    private ?Student $student = null;

    #[ORM\ManyToOne(inversedBy: 'enrollments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['enrollment:read', 'enrollment:write'])]
    private ?Grade $grade = null;

    #[ORM\ManyToOne(inversedBy: 'enrollments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['enrollment:read', 'enrollment:write'])]
    private ?Section $section = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Groups(['enrollment:read', 'enrollment:write'])]
    private ?int $academicYear = null;

    #[ORM\Column(length: 20)]
    #[Groups(['enrollment:read', 'enrollment:write'])]
    private string $status = 'active';

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['enrollment:read', 'enrollment:write'])]
    private ?\DateTimeInterface $enrollmentDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['enrollment:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['enrollment:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'enrollment', targetEntity: Payment::class)]
    private Collection $payments;

    #[ORM\OneToMany(mappedBy: 'enrollment', targetEntity: Contract::class)]
    private Collection $contracts;

    public function __construct()
    {
        $this->payments = new ArrayCollection();
        $this->contracts = new ArrayCollection();
        $this->academicYear = (int) date('Y');
        $this->enrollmentDate = new \DateTime();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): static
    {
        $this->student = $student;
        return $this;
    }

    public function getGrade(): ?Grade
    {
        return $this->grade;
    }

    public function setGrade(?Grade $grade): static
    {
        $this->grade = $grade;
        return $this;
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

    public function getAcademicYear(): ?int
    {
        return $this->academicYear;
    }

    public function setAcademicYear(int $academicYear): static
    {
        $this->academicYear = $academicYear;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getEnrollmentDate(): ?\DateTimeInterface
    {
        return $this->enrollmentDate;
    }

    public function setEnrollmentDate(?\DateTimeInterface $enrollmentDate): static
    {
        $this->enrollmentDate = $enrollmentDate;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return Collection<int, Payment>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): static
    {
        if (!$this->payments->contains($payment)) {
            $this->payments->add($payment);
            $payment->setEnrollment($this);
        }

        return $this;
    }

    public function removePayment(Payment $payment): static
    {
        if ($this->payments->removeElement($payment)) {
            if ($payment->getEnrollment() === $this) {
                $payment->setEnrollment(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Contract>
     */
    public function getContracts(): Collection
    {
        return $this->contracts;
    }

    public function addContract(Contract $contract): static
    {
        if (!$this->contracts->contains($contract)) {
            $this->contracts->add($contract);
            $contract->setEnrollment($this);
        }

        return $this;
    }

    public function removeContract(Contract $contract): static
    {
        if ($this->contracts->removeElement($contract)) {
            if ($contract->getEnrollment() === $this) {
                $contract->setEnrollment(null);
            }
        }

        return $this;
    }

    public function getTotalPaid(): float
    {
        $total = 0;
        foreach ($this->payments as $payment) {
            if ($payment->getStatus() === 'paid') {
                $total += $payment->getAmount();
            }
        }
        return $total;
    }

    public function getTotalPending(): float
    {
        $total = 0;
        foreach ($this->payments as $payment) {
            if ($payment->getStatus() === 'pending') {
                $total += $payment->getAmount();
            }
        }
        return $total;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
