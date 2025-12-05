<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Chat\Command\CreateRoomCommand;
use App\Application\Chat\Command\MarkMessageAsReadCommand;
use App\Application\Chat\Command\SendMessageCommand;
use App\Application\Chat\Query\GetRoomMessagesQuery;
use App\Application\Chat\Query\GetUnreadMessagesQuery;
use App\Application\Chat\Query\GetUserRoomsQuery;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/chat')]
class ChatController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $queryBus,
        private readonly MessageBusInterface $commandBus
    ) {}

    private function getAuthenticatedUser(): ?User
    {
        $user = $this->getUser();
        return $user instanceof User ? $user : null;
    }

    private function respond(mixed $data, int $status = 200): JsonResponse
    {
        return $this->json($data, $status);
    }

    private function respondNotAuthenticated(): JsonResponse
    {
        return $this->respond(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
    }

    // ===== Rooms =====
    #[Route('/rooms', name: 'api_chat_rooms', methods: ['GET'])]
    public function rooms(): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) return $this->respondNotAuthenticated();

        $rooms = $this->handleQuery(new GetUserRoomsQuery($user->getId()));
        return $this->respond($rooms, Response::HTTP_OK);
    }

    #[Route('/rooms', name: 'api_chat_create_room', methods: ['POST'])]
    public function createRoom(Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) return $this->respondNotAuthenticated();

        $data = json_decode($request->getContent(), true);
        if (!isset($data['participantId'])) {
            return $this->respond(['error' => 'Participant ID required'], Response::HTTP_BAD_REQUEST);
        }

        $result = $this->handleCommand(new CreateRoomCommand(
            userId: $user->getId(),
            participantId: $data['participantId'],
            name: $data['name'] ?? null
        ));

        $status = $result['existing'] ? Response::HTTP_OK : Response::HTTP_CREATED;
        return $this->respond($result['room'], $status);
    }

    // ===== Messages =====
    #[Route('/rooms/{id}/messages', name: 'api_chat_messages', methods: ['GET'])]
    public function messages(int $id, Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) return $this->respondNotAuthenticated();

        $limit = $request->query->getInt('limit', 50);
        $result = $this->handleQuery(new GetRoomMessagesQuery($id, $user->getId(), $limit));

        if (isset($result['error'])) {
            return $this->respond(['error' => $result['error']], $result['code']);
        }

        return $this->respond($result['messages'], Response::HTTP_OK);
    }

    #[Route('/rooms/{id}/messages', name: 'api_chat_send_message', methods: ['POST'])]
    public function sendMessage(int $id, Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) return $this->respondNotAuthenticated();

        $data = json_decode($request->getContent(), true);
        if (empty($data['content'])) {
            return $this->respond(['error' => 'Content required'], Response::HTTP_BAD_REQUEST);
        }

        $result = $this->handleCommand(new SendMessageCommand(
            roomId: $id,
            sender: $user,
            content: $data['content'],
            attachments: $data['attachments'] ?? null
        ));

        if (isset($result['error'])) {
            return $this->respond(['error' => $result['error']], $result['code']);
        }

        return $this->respond($result['message'], Response::HTTP_CREATED);
    }

    #[Route('/messages/{id}/read', name: 'api_chat_mark_read', methods: ['POST'])]
    public function markAsRead(int $id): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) return $this->respondNotAuthenticated();

        $result = $this->handleCommand(new MarkMessageAsReadCommand($id, $user->getId()));

        if (isset($result['error'])) {
            return $this->respond(['error' => $result['error']], $result['code']);
        }

        return $this->respond([
            'message' => 'Message marked as read',
            'chatMessage' => $result['message']
        ], Response::HTTP_OK);
    }

    #[Route('/rooms/{id}/unread', name: 'api_chat_unread', methods: ['GET'])]
    public function unreadMessages(int $id): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) return $this->respondNotAuthenticated();

        $result = $this->handleQuery(new GetUnreadMessagesQuery($id, $user->getId()));

        if (isset($result['error'])) {
            return $this->respond(['error' => $result['error']], $result['code']);
        }

        return $this->respond([
            'messages' => $result['messages'],
            'count' => $result['count']
        ], Response::HTTP_OK);
    }

    private function handleQuery(object $query): mixed
    {
        $envelope = $this->queryBus->dispatch($query);
        $handledStamp = $envelope->last(HandledStamp::class);
        return $handledStamp?->getResult();
    }

    private function handleCommand(object $command): mixed
    {
        $envelope = $this->commandBus->dispatch($command);
        $handledStamp = $envelope->last(HandledStamp::class);
        return $handledStamp?->getResult();
    }
}
