<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\ContractRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContractRepository::class)]
#[ORM\Table(name: 'contracts')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['contract:read']],
    denormalizationContext: ['groups' => ['contract:write']]
)]
class Contract
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['contract:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'contracts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['contract:read', 'contract:write'])]
    private ?Enrollment $enrollment = null;

    #[ORM\ManyToOne(inversedBy: 'contracts')]
    #[Groups(['contract:read', 'contract:write'])]
    private ?ParentEntity $parent = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Groups(['contract:read', 'contract:write'])]
    private ?string $contractNumber = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['contract:read', 'contract:write'])]
    private ?string $resolutionNumber = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    #[Groups(['contract:read', 'contract:write'])]
    private ?string $totalAmount = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['contract:read', 'contract:write'])]
    private ?int $installments = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['contract:read', 'contract:write'])]
    private ?string $generatedPdf = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['contract:read', 'contract:write'])]
    private ?string $signedPdf = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['contract:read', 'contract:write'])]
    private ?array $signatureMetadata = null;

    #[ORM\Column(length: 20)]
    #[Groups(['contract:read', 'contract:write'])]
    private string $status = 'pending'; // pending, signed, active, cancelled

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['contract:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['contract:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->contractNumber = $this->generateContractNumber();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    private function generateContractNumber(): string
    {
        return 'CONT-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(4)));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEnrollment(): ?Enrollment
    {
        return $this->enrollment;
    }

    public function setEnrollment(?Enrollment $enrollment): static
    {
        $this->enrollment = $enrollment;
        return $this;
    }

    public function getParent(): ?ParentEntity
    {
        return $this->parent;
    }

    public function setParent(?ParentEntity $parent): static
    {
        $this->parent = $parent;
        return $this;
    }

    public function getContractNumber(): ?string
    {
        return $this->contractNumber;
    }

    public function setContractNumber(string $contractNumber): static
    {
        $this->contractNumber = $contractNumber;
        return $this;
    }

    public function getResolutionNumber(): ?string
    {
        return $this->resolutionNumber;
    }

    public function setResolutionNumber(?string $resolutionNumber): static
    {
        $this->resolutionNumber = $resolutionNumber;
        return $this;
    }

    public function getTotalAmount(): ?string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(string $totalAmount): static
    {
        $this->totalAmount = $totalAmount;
        return $this;
    }

    public function getInstallments(): ?int
    {
        return $this->installments;
    }

    public function setInstallments(?int $installments): static
    {
        $this->installments = $installments;
        return $this;
    }

    public function getGeneratedPdf(): ?string
    {
        return $this->generatedPdf;
    }

    public function setGeneratedPdf(?string $generatedPdf): static
    {
        $this->generatedPdf = $generatedPdf;
        return $this;
    }

    public function getSignedPdf(): ?string
    {
        return $this->signedPdf;
    }

    public function setSignedPdf(?string $signedPdf): static
    {
        $this->signedPdf = $signedPdf;
        return $this;
    }

    public function getSignatureMetadata(): ?array
    {
        return $this->signatureMetadata;
    }

    public function setSignatureMetadata(?array $signatureMetadata): static
    {
        $this->signatureMetadata = $signatureMetadata;
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

    public function isSigned(): bool
    {
        return $this->status === 'signed' || $this->status === 'active';
    }

    public function getInstallmentAmount(): ?float
    {
        if (!$this->installments || $this->installments === 0) {
            return null;
        }
        
        return (float) $this->totalAmount / $this->installments;
    }

    public function markAsSigned(array $metadata): static
    {
        $this->status = 'signed';
        $this->signatureMetadata = array_merge($this->signatureMetadata ?? [], $metadata, [
            'signed_at' => (new \DateTime())->format('Y-m-d H:i:s')
        ]);
        return $this;
    }
}
