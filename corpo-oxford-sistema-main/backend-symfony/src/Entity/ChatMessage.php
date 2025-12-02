<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ChatMessageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ChatMessageRepository::class)]
#[ORM\Table(name: 'chat_messages')]
#[ApiResource(
    normalizationContext: ['groups' => ['chatmessage:read']],
    denormalizationContext: ['groups' => ['chatmessage:write']]
)]
class ChatMessage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['chatmessage:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['chatmessage:read', 'chatmessage:write'])]
    private ?ChatRoom $room = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['chatmessage:read', 'chatmessage:write'])]
    private ?User $sender = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['chatmessage:read', 'chatmessage:write'])]
    private ?string $content = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['chatmessage:read', 'chatmessage:write'])]
    private ?array $attachments = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['chatmessage:read', 'chatmessage:write'])]
    private ?array $readBy = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['chatmessage:read'])]
    private ?\DateTimeInterface $sentAt = null;

    public function __construct()
    {
        $this->sentAt = new \DateTime();
        $this->readBy = [];
        $this->attachments = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoom(): ?ChatRoom
    {
        return $this->room;
    }

    public function setRoom(?ChatRoom $room): static
    {
        $this->room = $room;
        return $this;
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): static
    {
        $this->sender = $sender;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function getAttachments(): ?array
    {
        return $this->attachments;
    }

    public function setAttachments(?array $attachments): static
    {
        $this->attachments = $attachments;
        return $this;
    }

    public function addAttachment(array $attachment): static
    {
        $this->attachments[] = $attachment;
        return $this;
    }

    public function getReadBy(): ?array
    {
        return $this->readBy;
    }

    public function setReadBy(?array $readBy): static
    {
        $this->readBy = $readBy;
        return $this;
    }

    public function markAsReadBy(int $userId): static
    {
        if (!in_array($userId, $this->readBy ?? [])) {
            $this->readBy[] = $userId;
        }
        return $this;
    }

    public function isReadBy(int $userId): bool
    {
        return in_array($userId, $this->readBy ?? []);
    }

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt(\DateTimeInterface $sentAt): static
    {
        $this->sentAt = $sentAt;
        return $this;
    }
}
