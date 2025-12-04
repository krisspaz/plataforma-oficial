<?php

declare(strict_types=1);

namespace App\Application\Coordination\Command;

use App\Domain\Coordination\Entity\Announcement;
use App\Domain\Coordination\Repository\AnnouncementRepositoryInterface;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreateAnnouncementHandler
{
    public function __construct(
        private readonly AnnouncementRepositoryInterface $announcementRepository,
        private readonly UserRepository $userRepository
    ) {}

    public function __invoke(CreateAnnouncementCommand $command): void
    {
        $author = $this->userRepository->find($command->authorId);

        if (!$author) {
            throw new \InvalidArgumentException('Author not found');
        }

        $expiresAt = $command->expiresAt ? new DateTimeImmutable($command->expiresAt) : null;

        $announcement = new Announcement(
            $command->title,
            $command->content,
            $command->type,
            $author,
            $command->targetIds,
            $expiresAt
        );

        $this->announcementRepository->save($announcement);
    }
}
