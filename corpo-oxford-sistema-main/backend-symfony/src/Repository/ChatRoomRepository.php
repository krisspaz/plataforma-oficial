<?php

namespace App\Repository;

use App\Entity\ChatRoom;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ChatRoom>
 */
class ChatRoomRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChatRoom::class);
    }

    /**
     * Find rooms where user is participant
     */
    public function findByParticipant(int $userId): array
    {
        return $this->createQueryBuilder('cr')
            ->where('JSON_CONTAINS(cr.participants, :userId) = 1')
            ->setParameter('userId', json_encode($userId))
            ->orderBy('cr.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find one-to-one room between two users
     */
    public function findOneToOneRoom(int $userId1, int $userId2): ?ChatRoom
    {
        return $this->createQueryBuilder('cr')
            ->where('cr.type = :type')
            ->andWhere('JSON_CONTAINS(cr.participants, :user1) = 1')
            ->andWhere('JSON_CONTAINS(cr.participants, :user2) = 1')
            ->setParameter('type', 'one_to_one')
            ->setParameter('user1', json_encode($userId1))
            ->setParameter('user2', json_encode($userId2))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
