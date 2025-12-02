<?php

namespace App\Repository;

use App\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * Find unread notifications for user
     */
    public function findUnreadByUser(int $userId): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.user = :userId')
            ->andWhere('n.read = :read')
            ->setParameter('userId', $userId)
            ->setParameter('read', false)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Mark all as read for user
     */
    public function markAllAsReadForUser(int $userId): void
    {
        $this->createQueryBuilder('n')
            ->update()
            ->set('n.read', ':read')
            ->where('n.user = :userId')
            ->setParameter('read', true)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->execute();
    }
}
