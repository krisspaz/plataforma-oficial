<?php

namespace App\Repository;

use App\Entity\Contract;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contract>
 */
class ContractRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contract::class);
    }

    /**
     * Find contracts by status
     */
    public function findByStatus(string $status): array
    {
        return $this->findBy(['status' => $status], ['createdAt' => 'DESC']);
    }

    /**
     * Find pending contracts (not signed)
     */
    public function findPending(): array
    {
        return $this->findByStatus('pending');
    }

    /**
     * Find contracts by student
     */
    public function findByStudent(int $studentId): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.enrollment', 'e')
            ->where('e.student = :studentId')
            ->setParameter('studentId', $studentId)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find contract by number
     */
    public function findOneByContractNumber(string $contractNumber): ?Contract
    {
        return $this->findOneBy(['contractNumber' => $contractNumber]);
    }
}
