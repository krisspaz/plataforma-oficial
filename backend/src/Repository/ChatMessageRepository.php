<?php

namespace App\Repository;

use App\Entity\ChatMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ChatMessage>
 */
class ChatMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatMessage::class);
    }

    /**
     * Find messages by room
     */
    public function findByRoom(int $roomId, int $limit = 50): array
    {
        return $this->createQueryBuilder('cm')
            ->where('cm.room = :roomId')
            ->setParameter('roomId', $roomId)
            ->orderBy('cm.sentAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find unread messages for user in room
     */
    public function findUnreadInRoom(int $roomId, int $userId): array
    {
        return $this->createQueryBuilder('cm')
            ->where('cm.room = :roomId')
            ->andWhere('JSON_CONTAINS(cm.readBy, :userId) = 0')
            ->setParameter('roomId', $roomId)
            ->setParameter('userId', json_encode($userId))
            ->orderBy('cm.sentAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
