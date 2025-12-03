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
    ) {
    }

    #[Route('/rooms', name: 'api_chat_rooms', methods: ['GET'])]
    public function rooms(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->json(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $rooms = $this->chatRoomRepository->findByParticipant($user->getId());
        
        return $this->json($rooms, Response::HTTP_OK, [], [
            'groups' => ['chatroom:read']
        ]);
    }

    #[Route('/rooms', name: 'api_chat_create_room', methods: ['POST'])]
    public function createRoom(Request $request): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->json(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['participantId'])) {
            return $this->json(['error' => 'Participant ID required'], Response::HTTP_BAD_REQUEST);
        }

        // Check if one-to-one room already exists
        $existingRoom = $this->chatRoomRepository->findOneToOneRoom(
            $user->getId(),
            $data['participantId']
        );

        if ($existingRoom) {
            return $this->json($existingRoom, Response::HTTP_OK, [], [
                'groups' => ['chatroom:read']
            ]);
        }

        // Create new room
        $room = new ChatRoom();
        $room->setType('one_to_one');
        $room->setParticipants([$user->getId(), $data['participantId']]);

        if (isset($data['name'])) {
            $room->setName($data['name']);
        }

        $this->entityManager->persist($room);
        $this->entityManager->flush();

        return $this->json($room, Response::HTTP_CREATED, [], [
            'groups' => ['chatroom:read']
        ]);
    }

    #[Route('/rooms/{id}/messages', name: 'api_chat_messages', methods: ['GET'])]
    public function messages(int $id, Request $request): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->json(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $room = $this->chatRoomRepository->find($id);
        
        if (!$room) {
            return $this->json(['error' => 'Room not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$room->isParticipant($user->getId())) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $limit = $request->query->getInt('limit', 50);
        $messages = $this->chatMessageRepository->findByRoom($id, $limit);

        return $this->json($messages, Response::HTTP_OK, [], [
            'groups' => ['chatmessage:read']
        ]);
    }

    #[Route('/rooms/{id}/messages', name: 'api_chat_send_message', methods: ['POST'])]
    public function sendMessage(int $id, Request $request): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->json(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $room = $this->chatRoomRepository->find($id);
        
        if (!$room) {
            return $this->json(['error' => 'Room not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$room->isParticipant($user->getId())) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['content'])) {
            return $this->json(['error' => 'Content required'], Response::HTTP_BAD_REQUEST);
        }

        $message = new ChatMessage();
        $message->setRoom($room);
        $message->setSender($user);
        $message->setContent($data['content']);

        if (isset($data['attachments'])) {
            $message->setAttachments($data['attachments']);
        }

        $this->entityManager->persist($message);
        $this->entityManager->flush();

        // Notify other participants
        $participants = $room->getParticipants() ?? [];
        foreach ($participants as $participantId) {
            if ($participantId !== $user->getId()) {
                $participant = $this->userRepository->find($participantId);
                if ($participant) {
                    $preview = strlen($data['content']) > 50 
                        ? substr($data['content'], 0, 50) . '...' 
                        : $data['content'];
                    
                    $this->notificationService->notifyNewMessage(
                        $participant,
                        $user->getFirstName() . ' ' . $user->getLastName(),
                        $preview
                    );
                }
            }
        }

        // TODO: Publish to Mercure Hub for real-time updates
        // $this->publishToMercure($room, $message);

        return $this->json($message, Response::HTTP_CREATED, [], [
            'groups' => ['chatmessage:read']
        ]);
    }

    #[Route('/messages/{id}/read', name: 'api_chat_mark_read', methods: ['POST'])]
    public function markAsRead(int $id): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->json(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $message = $this->chatMessageRepository->find($id);
        
        if (!$message) {
            return $this->json(['error' => 'Message not found'], Response::HTTP_NOT_FOUND);
        }

        $message->markAsReadBy($user->getId());
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Message marked as read',
            'chatMessage' => $message
        ], Response::HTTP_OK, [], [
            'groups' => ['chatmessage:read']
        ]);
    }

    #[Route('/rooms/{id}/unread', name: 'api_chat_unread', methods: ['GET'])]
    public function unreadMessages(int $id): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->json(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $room = $this->chatRoomRepository->find($id);
        
        if (!$room) {
            return $this->json(['error' => 'Room not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$room->isParticipant($user->getId())) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $unreadMessages = $this->chatMessageRepository->findUnreadInRoom($id, $user->getId());

        return $this->json([
            'messages' => $unreadMessages,
            'count' => count($unreadMessages)
        ], Response::HTTP_OK, [], [
            'groups' => ['chatmessage:read']
        ]);
    }

    private function publishToMercure(ChatRoom $room, ChatMessage $message): void
    {
        // TODO: Implement Mercure publishing
        // This would publish the message to all subscribers of the room
        // Example:
        // $update = new Update(
        //     '/chat/room/' . $room->getId(),
        //     json_encode(['message' => $message])
        // );
        // $this->mercureHub->publish($update);
    }
}
