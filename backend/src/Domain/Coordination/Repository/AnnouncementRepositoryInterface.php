<?php

declare(strict_types=1);

namespace App\Domain\Coordination\Repository;

use App\Domain\Coordination\Entity\Announcement;

interface AnnouncementRepositoryInterface
{
    public function save(Announcement $announcement): void;
    public function remove(Announcement $announcement): void;
    public function findActive(): array;
    public function findByType(string $type): array;
}
