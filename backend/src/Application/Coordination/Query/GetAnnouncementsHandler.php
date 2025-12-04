<?php

declare(strict_types=1);

namespace App\Application\Coordination\Query;

use App\Application\Coordination\DTO\AnnouncementDTO;
use App\Domain\Coordination\Repository\AnnouncementRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetAnnouncementsHandler
{
    public function __construct(
        private readonly AnnouncementRepositoryInterface $announcementRepository
    ) {}

    public function __invoke(GetAnnouncementsQuery $query): array
    {
        if ($query->type) {
            $announcements = $this->announcementRepository->findByType($query->type);
        } else {
            $announcements = $this->announcementRepository->findActive();
        }

        return array_map(
            fn($announcement) => AnnouncementDTO::fromEntity($announcement),
            $announcements
        );
    }
}
