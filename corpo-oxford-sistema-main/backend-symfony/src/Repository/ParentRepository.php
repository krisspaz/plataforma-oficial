<?php

namespace App\Repository;

use App\Entity\ParentEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ParentEntity>
 */
class ParentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ParentEntity::class);
    }

    /**
     * Find parents by student
     */
    public function findByStudent(int $studentId): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.students', 's')
            ->where('s.id = :studentId')
            ->setParameter('studentId', $studentId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Search parents by name or personal ID
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.user', 'u')
            ->where('u.firstName LIKE :query')
            ->orWhere('u.lastName LIKE :query')
            ->orWhere('p.personalId LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('u.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
