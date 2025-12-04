<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Traits\ApiResponseTrait;
use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use App\Service\NotificationService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/api/notifications')]
#[OA\Tag(name: 'Notifications')]
class NotificationController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        private readonly NotificationService $notificationService,
        private readonly LoggerInterface $logger,
    ) {}

    #[Route('', name: 'api_notifications_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/notifications',
        summary: 'Get user notifications with pagination',
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer', default: 20)),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return $this->unauthorized();
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 20)));

        $notifications = $this->notificationRepository->findPaginatedByUser(
            $user->getId(),
            $page,
            $limit
        );

        $total = $this->notificationRepository->countByUser($user->getId());

        $this->logger->info('User notifications retrieved', [
            'user_id' => $user->getId(),
            'page' => $page,
            'total' => $total,
        ]);

        return $this->paginated(
            $notifications,
            $total,
            $page,
            $limit,
            ['notification:read']
        );
    }

    #[Route('/unread', name: 'api_notifications_unread', methods: ['GET'])]
    #[OA\Get(
        path: '/api/notifications/unread',
        summary: 'Get unread notifications'
    )]
    public function unread(): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return $this->unauthorized();
        }

        $notifications = $this->notificationRepository->findUnreadByUser($user->getId());
        $count = count($notifications);

        $this->logger->info('Unread notifications retrieved', [
            'user_id' => $user->getId(),
            'count' => $count,
        ]);

        return $this->success([
            'notifications' => $notifications,
            'count' => $count,
        ], 200, [], ['notification:read']);
    }

    #[Route('/{id}/mark-read', name: 'api_notifications_mark_read', methods: ['POST'])]
    #[OA\Post(
        path: '/api/notifications/{id}/mark-read',
        summary: 'Mark notification as read'
    )]
    public function markAsRead(int $id): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return $this->unauthorized();
        }

        $notification = $this->notificationRepository->find($id);
        
        if (!$notification) {
            $this->logger->warning('Notification not found', ['id' => $id]);
            return $this->notFound('Notification');
        }

        if (!$this->canAccessNotification($notification, $user)) {
            $this->logger->warning('Unauthorized notification access attempt', [
                'user_id' => $user->getId(),
                'notification_id' => $id,
            ]);
            return $this->forbidden('You cannot access this notification');
        }

        $this->notificationService->markAsRead($notification);

        $this->logger->info('Notification marked as read', [
            'user_id' => $user->getId(),
            'notification_id' => $id,
        ]);

        return $this->success([
            'message' => 'Notification marked as read',
            'notification' => $notification,
        ], 200, [], ['notification:read']);
    }

    #[Route('/mark-all-read', name: 'api_notifications_mark_all_read', methods: ['POST'])]
    #[OA\Post(
        path: '/api/notifications/mark-all-read',
        summary: 'Mark all notifications as read'
    )]
    public function markAllAsRead(): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return $this->unauthorized();
        }

        $count = $this->notificationService->markAllAsRead($user);

        $this->logger->info('All notifications marked as read', [
            'user_id' => $user->getId(),
            'count' => $count,
        ]);

        return $this->success([
            'message' => 'All notifications marked as read',
            'count' => $count,
        ]);
    }

    #[Route('/{id}', name: 'api_notifications_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/notifications/{id}',
        summary: 'Delete a notification'
    )]
    public function delete(int $id): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return $this->unauthorized();
        }

        $notification = $this->notificationRepository->find($id);
        
        if (!$notification) {
            return $this->notFound('Notification');
        }

        if (!$this->canAccessNotification($notification, $user)) {
            return $this->forbidden('You cannot delete this notification');
        }

        $this->notificationService->delete($notification);

        $this->logger->info('Notification deleted', [
            'user_id' => $user->getId(),
            'notification_id' => $id,
        ]);

        return $this->noContent();
    }

    private function getAuthenticatedUser(): ?User
    {
        $user = $this->getUser();
        return $user instanceof User ? $user : null;
    }

    private function canAccessNotification(Notification $notification, User $user): bool
    {
        return $notification->getUser()->getId() === $user->getId();
    }
}
