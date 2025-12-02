<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use App\Repository\PaymentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[ORM\Table(name: 'payments')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
        new Post(),
        new Put(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['payment:read']],
    denormalizationContext: ['groups' => ['payment:write']]
)]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['payment:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['payment:read', 'payment:write'])]
    private ?Enrollment $enrollment = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank]
    #[Assert\Positive]
    #[Groups(['payment:read', 'payment:write'])]
    private ?string $amount = null;

    #[ORM\Column(length: 20)]
    #[Groups(['payment:read', 'payment:write'])]
    private ?string $paymentType = null; // contado, credito, cuotas

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['payment:read', 'payment:write'])]
    private ?string $paymentMethod = null; // efectivo, tarjeta, transferencia, stripe, bac, paypal

    #[ORM\Column(length: 20)]
    #[Groups(['payment:read', 'payment:write'])]
    private string $status = 'pending'; // pending, paid, cancelled

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['payment:read', 'payment:write'])]
    private ?\DateTimeInterface $dueDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['payment:read', 'payment:write'])]
    private ?\DateTimeInterface $paidDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['payment:read', 'payment:write'])]
    private ?string $receipt = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['payment:read', 'payment:write'])]
    private ?array $metadata = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['payment:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['payment:read'])]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
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

    public function getEnrollment(): ?Enrollment
    {
        return $this->enrollment;
    }

    public function setEnrollment(?Enrollment $enrollment): static
    {
        $this->enrollment = $enrollment;
        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;
        return $this;
    }

    public function getPaymentType(): ?string
    {
        return $this->paymentType;
    }

    public function setPaymentType(?string $paymentType): static
    {
        $this->paymentType = $paymentType;
        return $this;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?string $paymentMethod): static
    {
        $this->paymentMethod = $paymentMethod;
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

    public function getDueDate(): ?\DateTimeInterface
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTimeInterface $dueDate): static
    {
        $this->dueDate = $dueDate;
        return $this;
    }

    public function getPaidDate(): ?\DateTimeInterface
    {
        return $this->paidDate;
    }

    public function setPaidDate(?\DateTimeInterface $paidDate): static
    {
        $this->paidDate = $paidDate;
        return $this;
    }

    public function getReceipt(): ?string
    {
        return $this->receipt;
    }

    public function setReceipt(?string $receipt): static
    {
        $this->receipt = $receipt;
        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): static
    {
        $this->metadata = $metadata;
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

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isOverdue(): bool
    {
        if (!$this->dueDate || $this->isPaid()) {
            return false;
        }
        
        return $this->dueDate < new \DateTime();
    }

    public function markAsPaid(): static
    {
        $this->status = 'paid';
        $this->paidDate = new \DateTime();
        return $this;
    }
}
