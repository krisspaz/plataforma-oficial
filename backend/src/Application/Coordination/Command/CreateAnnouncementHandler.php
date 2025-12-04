<?php

declare(strict_types=1);

namespace App\Application\Coordination\Command;

use App\Domain\Coordination\Entity\Announcement;
use App\Domain\Coordination\Repository\AnnouncementRepositoryInterface;
use App\Infrastructure\Cache\CacheService;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreateAnnouncementHandler
{
    public function __construct(
        private readonly AnnouncementRepositoryInterface $repository,
        private readonly UserRepository $userRepository,
        private readonly CacheService $cache
    ) {}

    public function __invoke(CreateAnnouncementCommand $command): void
    {
        $author = $this->userRepository->find($command->authorId);

        if (!$author) {
            throw new \InvalidArgumentException('Author not found');
        }

        $announcement = new Announcement(
            $command->title,
            $command->content,
            $command->type,
            $author,
            $command->expiresAt,
            $command->targetIds
        );

        $this->repository->save($announcement);

        // Invalidate announcements cache
        $this->cache->invalidateAnnouncements($command->type);
    }
}
