<?php

declare(strict_types=1);

namespace App\Application\Coordination\Command;

use DateTimeImmutable;

final class CreateAnnouncementCommand
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
        public readonly string $type,
        public readonly int $authorId,
        public readonly ?array $targetIds = null,
        public readonly ?string $expiresAt = null
    ) {}
}
