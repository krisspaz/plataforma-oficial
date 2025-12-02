<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\ParentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ParentRepository::class)]
#[ORM\Table(name: 'parents')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['parent:read']],
    denormalizationContext: ['groups' => ['parent:write']]
)]
class ParentEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['parent:read'])]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'parent', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['parent:read', 'parent:write'])]
    private ?User $user = null;

    #[ORM\Column(length: 20, unique: true, nullable: true)]
    #[Groups(['parent:read', 'parent:write'])]
    private ?string $personalId = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['parent:read', 'parent:write'])]
    private ?string $profession = null;

    #[ORM\Column(length: 200, nullable: true)]
    #[Groups(['parent:read', 'parent:write'])]
    private ?string $workplace = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['parent:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['parent:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToMany(targetEntity: Student::class, inversedBy: 'parents')]
    #[ORM\JoinTable(name: 'parent_student')]
    private Collection $students;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: Contract::class)]
    private Collection $contracts;

    public function __construct()
    {
        $this->students = new ArrayCollection();
        $this->contracts = new ArrayCollection();
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

    public function getProfession(): ?string
    {
        return $this->profession;
    }

    public function setProfession(?string $profession): static
    {
        $this->profession = $profession;
        return $this;
    }

    public function getWorkplace(): ?string
    {
        return $this->workplace;
    }

    public function setWorkplace(?string $workplace): static
    {
        $this->workplace = $workplace;
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
     * @return Collection<int, Student>
     */
    public function getStudents(): Collection
    {
        return $this->students;
    }

    public function addStudent(Student $student): static
    {
        if (!$this->students->contains($student)) {
            $this->students->add($student);
        }

        return $this;
    }

    public function removeStudent(Student $student): static
    {
        $this->students->removeElement($student);

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
            $contract->setParent($this);
        }

        return $this;
    }

    public function removeContract(Contract $contract): static
    {
        if ($this->contracts->removeElement($contract)) {
            if ($contract->getParent() === $this) {
                $contract->setParent(null);
            }
        }

        return $this;
    }
}
