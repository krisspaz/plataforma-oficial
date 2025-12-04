<?php

declare(strict_types=1);

namespace App\Application\Coordination\Query;

use App\Application\Coordination\DTO\AnnouncementDTO;
use App\Domain\Coordination\Repository\AnnouncementRepositoryInterface;
use App\Infrastructure\Cache\CacheService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetAnnouncementsHandler
{
    public function __construct(
        private readonly AnnouncementRepositoryInterface $repository,
        private readonly CacheService $cache
    ) {}

    public function __invoke(GetAnnouncementsQuery $query): array
    {
        return $this->cache->getAnnouncements(
            $query->type,
            fn() => array_map(
                fn($announcement) => AnnouncementDTO::fromEntity($announcement),
                $this->repository->findActive($query->type)
            )
        );
    }
}
