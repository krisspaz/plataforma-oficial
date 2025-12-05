<?php

declare(strict_types=1);

namespace App\Application\Chat\Query;

use App\Repository\ChatRoomRepository;
use App\Repository\ChatMessageRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetRoomMessagesHandler
{
    public function __construct(
        private readonly ChatRoomRepository $chatRoomRepository,
        private readonly ChatMessageRepository $chatMessageRepository
    ) {}

    public function __invoke(GetRoomMessagesQuery $query): array
    {
        $room = $this->chatRoomRepository->find($query->roomId);

        if (!$room) {
            return ['error' => 'Room not found', 'code' => 404];
        }

        if (!$room->isParticipant($query->userId)) {
            return ['error' => 'Unauthorized', 'code' => 403];
        }

        $messages = $this->chatMessageRepository->findByRoom($query->roomId, $query->limit);

        return ['messages' => $messages, 'code' => 200];
    }
}
