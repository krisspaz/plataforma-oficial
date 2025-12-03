<?php

namespace App\Repository;

use App\Entity\AIRiskScore;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AIRiskScore>
 */
class AIRiskScoreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AIRiskScore::class);
    }

    /**
     * Find latest score for student
     */
    public function findLatestForStudent(int $studentId): ?AIRiskScore
    {
        return $this->createQueryBuilder('ars')
            ->where('ars.student = :studentId')
            ->setParameter('studentId', $studentId)
            ->orderBy('ars.calculatedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find high risk students
     */
    public function findHighRiskStudents(): array
    {
        return $this->createQueryBuilder('ars')
            ->where('ars.riskLevel IN (:levels)')
            ->setParameter('levels', ['high', 'critical'])
            ->orderBy('ars.calculatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
