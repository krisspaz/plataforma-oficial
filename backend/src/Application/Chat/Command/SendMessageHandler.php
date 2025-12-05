<?php

declare(strict_types=1);

namespace App\Application\Chat\Command;

use App\Entity\ChatMessage;
use App\Repository\ChatRoomRepository;
use App\Repository\UserRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SendMessageHandler
{
    public function __construct(
        private readonly ChatRoomRepository $chatRoomRepository,
        private readonly UserRepository $userRepository,
        private readonly NotificationService $notificationService,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function __invoke(SendMessageCommand $command): array
    {
        $room = $this->chatRoomRepository->find($command->roomId);

        if (!$room) {
            return ['error' => 'Room not found', 'code' => 404];
        }

        if (!$room->isParticipant($command->sender->getId())) {
            return ['error' => 'Unauthorized', 'code' => 403];
        }

        $message = new ChatMessage();
        $message->setRoom($room);
        $message->setSender($command->sender);
        $message->setContent($command->content);
        $message->setAttachments($command->attachments);

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        // Notify participants
        $this->notifyParticipants($room, $message, $command->sender);

        return ['message' => $message, 'code' => 201];
    }

    private function notifyParticipants($room, $message, $sender): void
    {
        foreach ($room->getParticipants() ?? [] as $participantId) {
            if ($participantId === $sender->getId()) continue;
            $participant = $this->userRepository->find($participantId);
            if ($participant) {
                $preview = strlen($message->getContent()) > 50
                    ? substr($message->getContent(), 0, 50) . '...'
                    : $message->getContent();

                $this->notificationService->notifyNewMessage(
                    $participant,
                    $sender->getFullName(),
                    $preview
                );
            }
        }
    }
}
