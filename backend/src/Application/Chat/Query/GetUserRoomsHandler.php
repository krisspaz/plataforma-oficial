<?php

declare(strict_types=1);

namespace App\Application\Chat\Query;

use App\Repository\ChatRoomRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetUserRoomsHandler
{
    public function __construct(
        private readonly ChatRoomRepository $chatRoomRepository
    ) {}

    public function __invoke(GetUserRoomsQuery $query): array
    {
        return $this->chatRoomRepository->findByParticipant($query->userId);
    }
}
