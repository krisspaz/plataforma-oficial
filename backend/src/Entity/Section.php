<?php

namespace App\Entity;

use App\Repository\SectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SectionRepository::class)]
#[ORM\Table(name: 'sections')]
class Section
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['section:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'sections')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['section:read', 'section:write'])]
    private ?Grade $grade = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank]
    #[Groups(['section:read', 'section:write'])]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['section:read', 'section:write'])]
    private ?int $capacity = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Groups(['section:read', 'section:write'])]
    private ?int $academicYear = null;

    #[ORM\OneToMany(mappedBy: 'section', targetEntity: Enrollment::class)]
    private Collection $enrollments;

    public function __construct()
    {
        $this->enrollments = new ArrayCollection();
        $this->academicYear = (int) date('Y');
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(?int $capacity): static
    {
        $this->capacity = $capacity;
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

    /**
     * @return Collection<int, Enrollment>
     */
    public function getEnrollments(): Collection
    {
        return $this->enrollments;
    }

    public function addEnrollment(Enrollment $enrollment): static
    {
        if (!$this->enrollments->contains($enrollment)) {
            $this->enrollments->add($enrollment);
            $enrollment->setSection($this);
        }

        return $this;
    }

    public function removeEnrollment(Enrollment $enrollment): static
    {
        if ($this->enrollments->removeElement($enrollment)) {
            if ($enrollment->getSection() === $this) {
                $enrollment->setSection(null);
            }
        }

        return $this;
    }

    public function getCurrentEnrollmentCount(): int
    {
        return $this->enrollments->filter(function(Enrollment $enrollment) {
            return $enrollment->getStatus() === 'active';
        })->count();
    }

    public function hasAvailableSpace(): bool
    {
        if (!$this->capacity) {
            return true;
        }
        
        return $this->getCurrentEnrollmentCount() < $this->capacity;
    }

    public function __toString(): string
    {
        return $this->grade?->getName() . ' - Sección ' . $this->name;
    }
}
