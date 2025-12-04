<?php

declare(strict_types=1);

namespace App\Application\Coordination\DTO;

use App\Domain\Coordination\Entity\Announcement;

final class AnnouncementDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $content,
        public readonly string $type,
        public readonly string $authorName,
        public readonly string $createdAt,
        public readonly ?string $expiresAt,
        public readonly ?array $targetIds
    ) {}

    public static function fromEntity(Announcement $announcement): self
    {
        $author = $announcement->getAuthor();
        $authorName = sprintf('%s %s', $author->getFirstName(), $author->getLastName());

        return new self(
            (string) $announcement->getId(),
            $announcement->getTitle(),
            $announcement->getContent(),
            $announcement->getType(),
            $authorName,
            $announcement->getCreatedAt()->format('Y-m-d H:i:s'),
            $announcement->getExpiresAt()?->format('Y-m-d H:i:s'),
            $announcement->getTargetIds()
        );
    }
}
