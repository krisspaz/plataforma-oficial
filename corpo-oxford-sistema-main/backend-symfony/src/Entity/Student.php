<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\StudentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: StudentRepository::class)]
#[ORM\Table(name: 'students')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['student:read']],
    denormalizationContext: ['groups' => ['student:write']]
)]
class Student
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['student:read'])]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'student', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['student:read', 'student:write'])]
    private ?User $user = null;

    #[ORM\Column(length: 20, unique: true, nullable: true)]
    #[Groups(['student:read', 'student:write'])]
    private ?string $personalId = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['student:read', 'student:write'])]
    private ?\DateTimeInterface $birthDate = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups(['student:read', 'student:write'])]
    private ?string $gender = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['student:read', 'student:write'])]
    private ?string $nationality = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['student:read', 'student:write'])]
    private ?string $address = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['student:read', 'student:write'])]
    private ?array $emergencyContact = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['student:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['student:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToMany(targetEntity: ParentEntity::class, mappedBy: 'students')]
    private Collection $parents;

    #[ORM\OneToMany(mappedBy: 'student', targetEntity: Enrollment::class)]
    private Collection $enrollments;

    public function __construct()
    {
        $this->parents = new ArrayCollection();
        $this->enrollments = new ArrayCollection();
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getPersonalId(): ?string
    {
        return $this->personalId;
    }

    public function setPersonalId(?string $personalId): static
    {
        $this->personalId = $personalId;
        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTimeInterface $birthDate): static
    {
        $this->birthDate = $birthDate;
        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): static
    {
        $this->gender = $gender;
        return $this;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function setNationality(?string $nationality): static
    {
        $this->nationality = $nationality;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getEmergencyContact(): ?array
    {
        return $this->emergencyContact;
    }

    public function setEmergencyContact(?array $emergencyContact): static
    {
        $this->emergencyContact = $emergencyContact;
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
     * @return Collection<int, ParentEntity>
     */
    public function getParents(): Collection
    {
        return $this->parents;
    }

    public function addParent(ParentEntity $parent): static
    {
        if (!$this->parents->contains($parent)) {
            $this->parents->add($parent);
            $parent->addStudent($this);
        }

        return $this;
    }

    public function removeParent(ParentEntity $parent): static
    {
        if ($this->parents->removeElement($parent)) {
            $parent->removeStudent($this);
        }

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
            $enrollment->setStudent($this);
        }

        return $this;
    }

    public function removeEnrollment(Enrollment $enrollment): static
    {
        if ($this->enrollments->removeElement($enrollment)) {
            if ($enrollment->getStudent() === $this) {
                $enrollment->setStudent(null);
            }
        }

        return $this;
    }

    public function getAge(): ?int
    {
        if (!$this->birthDate) {
            return null;
        }
        
        return $this->birthDate->diff(new \DateTime())->y;
    }
}
