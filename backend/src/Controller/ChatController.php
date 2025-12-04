<?php

namespace App\Controller;

use App\Entity\ChatRoom;
use App\Entity\ChatMessage;
use App\Entity\User;
use App\Repository\ChatRoomRepository;
use App\Repository\ChatMessageRepository;
use App\Repository\UserRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/chat')]
class ChatController extends AbstractController
{
    public function __construct(
        private ChatRoomRepository $chatRoomRepository,
        private ChatMessageRepository $chatMessageRepository,
        private UserRepository $userRepository,
        private NotificationService $notificationService,
        private EntityManagerInterface $entityManager
    ) {}

    // ===== Helpers =====
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

    private function respondNotFound(string $message = 'Not found'): JsonResponse
    {
        return $this->respond(['error' => $message], Response::HTTP_NOT_FOUND);
    }

    private function notifyParticipants(ChatRoom $room, ChatMessage $message, User $sender): void
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

    // ===== Rooms =====
    #[Route('/rooms', name: 'api_chat_rooms', methods: ['GET'])]
    public function rooms(): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) return $this->respondNotAuthenticated();

        $rooms = $this->chatRoomRepository->findByParticipant($user->getId());
        return $this->respond($rooms, Response::HTTP_OK);
    }

    #[Route('/rooms', name: 'api_chat_create_room', methods: ['POST'])]
    public function createRoom(Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) return $this->respondNotAuthenticated();

        $data = json_decode($request->getContent(), true);
        if (!isset($data['participantId'])) return $this->respond(['error' => 'Participant ID required'], Response::HTTP_BAD_REQUEST);

        // Check for existing room
        $existingRoom = $this->chatRoomRepository->findOneToOneRoom($user->getId(), $data['participantId']);
        if ($existingRoom) return $this->respond($existingRoom, Response::HTTP_OK);

        $room = new ChatRoom();
        $room->setType('one_to_one');
        $room->setParticipants([$user->getId(), $data['participantId']]);
        if (isset($data['name'])) $room->setName($data['name']);

        $this->entityManager->persist($room);
        $this->entityManager->flush();

        return $this->respond($room, Response::HTTP_CREATED);
    }

    // ===== Messages =====
    #[Route('/rooms/{id}/messages', name: 'api_chat_messages', methods: ['GET'])]
    public function messages(int $id, Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) return $this->respondNotAuthenticated();

        $room = $this->chatRoomRepository->find($id);
        if (!$room) return $this->respondNotFound('Room not found');
        if (!$room->isParticipant($user->getId())) return $this->respond(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);

        $limit = $request->query->getInt('limit', 50);
        $messages = $this->chatMessageRepository->findByRoom($id, $limit);

        return $this->respond($messages, Response::HTTP_OK);
    }

    #[Route('/rooms/{id}/messages', name: 'api_chat_send_message', methods: ['POST'])]
    public function sendMessage(int $id, Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) return $this->respondNotAuthenticated();

        $room = $this->chatRoomRepository->find($id);
        if (!$room) return $this->respondNotFound('Room not found');
        if (!$room->isParticipant($user->getId())) return $this->respond(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);

        $data = json_decode($request->getContent(), true);
        if (empty($data['content'])) return $this->respond(['error' => 'Content required'], Response::HTTP_BAD_REQUEST);

        $message = new ChatMessage();
        $message->setRoom($room);
        $message->setSender($user);
        $message->setContent($data['content']);
        $message->setAttachments($data['attachments'] ?? null);

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        $this->notifyParticipants($room, $message, $user);

        return $this->respond($message, Response::HTTP_CREATED);
    }

    #[Route('/messages/{id}/read', name: 'api_chat_mark_read', methods: ['POST'])]
    public function markAsRead(int $id): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) return $this->respondNotAuthenticated();

        $message = $this->chatMessageRepository->find($id);
        if (!$message) return $this->respondNotFound('Message not found');

        $message->markAsReadBy($user->getId());
        $this->entityManager->flush();

        return $this->respond(['message' => 'Message marked as read', 'chatMessage' => $message], Response::HTTP_OK);
    }

    #[Route('/rooms/{id}/unread', name: 'api_chat_unread', methods: ['GET'])]
    public function unreadMessages(int $id): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) return $this->respondNotAuthenticated();

        $room = $this->chatRoomRepository->find($id);
        if (!$room) return $this->respondNotFound('Room not found');
        if (!$room->isParticipant($user->getId())) return $this->respond(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);

        $unreadMessages = $this->chatMessageRepository->findUnreadInRoom($id, $user->getId());
        return $this->respond(['messages' => $unreadMessages, 'count' => count($unreadMessages)], Response::HTTP_OK);
    }
}
