<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\ChatRoomRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ChatRoomRepository::class)]
#[ORM\Table(name: 'chat_rooms')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    normalizationContext: ['groups' => ['chatroom:read']],
    denormalizationContext: ['groups' => ['chatroom:write']]
)]
class ChatRoom
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['chatroom:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['chatroom:read', 'chatroom:write'])]
    private ?string $name = null;

    #[ORM\Column(length: 20)]
    #[Groups(['chatroom:read', 'chatroom:write'])]
    private string $type = 'one_to_one'; // one_to_one, group

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['chatroom:read', 'chatroom:write'])]
    private ?array $participants = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['chatroom:read', 'chatroom:write'])]
    private ?array $metadata = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['chatroom:read'])]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\OneToMany(mappedBy: 'room', targetEntity: ChatMessage::class, cascade: ['remove'])]
    private Collection $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->participants = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getParticipants(): ?array
    {
        return $this->participants;
    }

    public function setParticipants(?array $participants): static
    {
        $this->participants = $participants;
        return $this;
    }

    public function addParticipant(int $userId): static
    {
        if (!in_array($userId, $this->participants ?? [])) {
            $this->participants[] = $userId;
        }
        return $this;
    }

    public function removeParticipant(int $userId): static
    {
        $this->participants = array_values(array_filter(
            $this->participants ?? [],
            fn($id) => $id !== $userId
        ));
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

    /**
     * @return Collection<int, ChatMessage>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(ChatMessage $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setRoom($this);
        }

        return $this;
    }

    public function removeMessage(ChatMessage $message): static
    {
        if ($this->messages->removeElement($message)) {
            if ($message->getRoom() === $this) {
                $message->setRoom(null);
            }
        }

        return $this;
    }

    public function isParticipant(int $userId): bool
    {
        return in_array($userId, $this->participants ?? []);
    }
}
