<?php

namespace App\Repository;

use App\Entity\AuditLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AuditLog>
 */
class AuditLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuditLog::class);
    }

    /**
     * Find logs by user
     */
    public function findByUser(int $userId, int $limit = 100): array
    {
        return $this->createQueryBuilder('al')
            ->where('al.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('al.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find logs by entity
     */
    public function findByEntity(string $entity, int $entityId): array
    {
        return $this->createQueryBuilder('al')
            ->where('al.entity = :entity')
            ->andWhere('al.entityId = :entityId')
            ->setParameter('entity', $entity)
            ->setParameter('entityId', $entityId)
            ->orderBy('al.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
