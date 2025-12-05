<?php

declare(strict_types=1);

namespace App\Application\Chat\Command;

use App\Entity\ChatRoom;
use App\Repository\ChatRoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreateRoomHandler
{
    public function __construct(
        private readonly ChatRoomRepository $chatRoomRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function __invoke(CreateRoomCommand $command): array
    {
        // Check for existing room
        $existingRoom = $this->chatRoomRepository->findOneToOneRoom($command->userId, $command->participantId);
        if ($existingRoom) {
            return ['room' => $existingRoom, 'existing' => true];
        }

        $room = new ChatRoom();
        $room->setType('one_to_one');
        $room->setParticipants([$command->userId, $command->participantId]);
        if ($command->name) {
            $room->setName($command->name);
        }

        $this->entityManager->persist($room);
        $this->entityManager->flush();

        return ['room' => $room, 'existing' => false];
    }
}
