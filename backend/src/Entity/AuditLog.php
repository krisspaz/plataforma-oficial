<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\AuditLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: AuditLogRepository::class)]
#[ORM\Table(name: 'audit_logs')]
#[ORM\Index(columns: ['created_at'], name: 'idx_audit_created')]
#[ORM\Index(columns: ['user_id'], name: 'idx_audit_user')]
#[ApiResource(
    normalizationContext: ['groups' => ['auditlog:read']],
    denormalizationContext: ['groups' => ['auditlog:write']]
)]
class AuditLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['auditlog:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'auditLogs')]
    #[Groups(['auditlog:read', 'auditlog:write'])]
    private ?User $user = null;

    #[ORM\Column(length: 100)]
    #[Groups(['auditlog:read', 'auditlog:write'])]
    private ?string $action = null;

    #[ORM\Column(length: 100)]
    #[Groups(['auditlog:read', 'auditlog:write'])]
    private ?string $entity = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['auditlog:read', 'auditlog:write'])]
    private ?int $entityId = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['auditlog:read', 'auditlog:write'])]
    private ?array $changes = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['auditlog:read'])]
    private ?array $oldData = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['auditlog:read'])]
    private ?array $newData = null;

    #[ORM\Column(length: 20)]
    #[Groups(['auditlog:read'])]
    private string $severity = 'info';

    #[ORM\Column(nullable: true)]
    #[Groups(['auditlog:read'])]
    private ?int $userId = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['auditlog:read'])]
    private ?string $userEmail = null;

    #[ORM\Column(length: 64)]
    private string $signature = '';

    #[ORM\Column(length: 45, nullable: true)]
    #[Groups(['auditlog:read', 'auditlog:write'])]
    private ?string $ip = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['auditlog:read', 'auditlog:write'])]
    private ?string $userAgent = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['auditlog:read'])]
    private ?\DateTimeInterface $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;
        return $this;
    }

    public function getEntity(): ?string
    {
        return $this->entity;
    }

    public function setEntity(string $entity): static
    {
        $this->entity = $entity;
        return $this;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(?int $entityId): static
    {
        $this->entityId = $entityId;
        return $this;
    }

    public function getChanges(): ?array
    {
        return $this->changes;
    }

    public function setChanges(?array $changes): static
    {
        $this->changes = $changes;
        return $this;
    }

    public function getOldData(): ?array
    {
        return $this->oldData;
    }

    public function setOldData(?array $oldData): static
    {
        $this->oldData = $oldData;
        return $this;
    }

    public function getNewData(): ?array
    {
        return $this->newData;
    }

    public function setNewData(?array $newData): static
    {
        $this->newData = $newData;
        return $this;
    }

    public function getSeverity(): string
    {
        return $this->severity;
    }

    public function setSeverity(string $severity): static
    {
        $this->severity = $severity;
        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(?int $userId): static
    {
        $this->userId = $userId;
        return $this;
    }

    public function getUserEmail(): ?string
    {
        return $this->userEmail;
    }

    public function setUserEmail(?string $userEmail): static
    {
        $this->userEmail = $userEmail;
        return $this;
    }

    public function getSignature(): string
    {
        return $this->signature;
    }

    public function setSignature(string $signature): static
    {
        $this->signature = $signature;
        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): static
    {
        $this->ip = $ip;
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): static
    {
        $this->userAgent = $userAgent;
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
}
