<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\NotificationRepository;
use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/notifications')]
class NotificationController extends AbstractController
{
    public function __construct(
        private NotificationRepository $notificationRepository,
        private NotificationService $notificationService
    ) {
    }

    #[Route('', name: 'api_notifications_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->json(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $notifications = $this->notificationRepository->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC']
        );
        
        return $this->json($notifications, Response::HTTP_OK, [], [
            'groups' => ['notification:read']
        ]);
    }

    #[Route('/unread', name: 'api_notifications_unread', methods: ['GET'])]
    public function unread(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->json(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $notifications = $this->notificationRepository->findUnreadByUser($user->getId());
        
        return $this->json([
            'notifications' => $notifications,
            'count' => count($notifications)
        ], Response::HTTP_OK, [], [
            'groups' => ['notification:read']
        ]);
    }

    #[Route('/{id}/mark-read', name: 'api_notifications_mark_read', methods: ['POST'])]
    public function markAsRead(int $id): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->json(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $notification = $this->notificationRepository->find($id);
        
        if (!$notification) {
            return $this->json(['error' => 'Notification not found'], Response::HTTP_NOT_FOUND);
        }

        if ($notification->getUser()->getId() !== $user->getId()) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $this->notificationService->markAsRead($notification);

        return $this->json([
            'message' => 'Notification marked as read',
            'notification' => $notification
        ], Response::HTTP_OK, [], [
            'groups' => ['notification:read']
        ]);
    }

    #[Route('/mark-all-read', name: 'api_notifications_mark_all_read', methods: ['POST'])]
    public function markAllAsRead(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user instanceof User) {
            return $this->json(['error' => 'Not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $this->notificationService->markAllAsRead($user);

        return $this->json([
            'message' => 'All notifications marked as read'
        ]);
    }
}
