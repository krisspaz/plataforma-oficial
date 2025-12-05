<?php

declare(strict_types=1);

namespace App\Application\Chat\Command;

use App\Repository\ChatMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class MarkMessageAsReadHandler
{
    public function __construct(
        private readonly ChatMessageRepository $chatMessageRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function __invoke(MarkMessageAsReadCommand $command): array
    {
        $message = $this->chatMessageRepository->find($command->messageId);

        if (!$message) {
            return ['error' => 'Message not found', 'code' => 404];
        }

        $message->markAsReadBy($command->userId);
        $this->entityManager->flush();

        return [
            'success' => true,
            'message' => $message,
            'code' => 200
        ];
    }
}
