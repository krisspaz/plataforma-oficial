<?php

declare(strict_types=1);

namespace App\Domain\Coordination\Entity;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'announcements')]
class Announcement
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\Column(length: 50)]
    private string $type; // 'general', 'teachers', 'parents', 'students', 'specific_grade'

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $targetIds = null; // e.g., list of grade IDs if type is specific_grade

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $author;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $expiresAt = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    public function __construct(
        string $title,
        string $content,
        string $type,
        User $author,
        ?array $targetIds = null,
        ?DateTimeImmutable $expiresAt = null
    ) {
        $this->id = Uuid::v7();
        $this->title = $title;
        $this->content = $content;
        $this->type = $type;
        $this->author = $author;
        $this->targetIds = $targetIds;
        $this->expiresAt = $expiresAt;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTargetIds(): ?array
    {
        return $this->targetIds;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getExpiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }
}
