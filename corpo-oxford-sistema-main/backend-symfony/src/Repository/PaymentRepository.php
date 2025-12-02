<?php

namespace App\Repository;

use App\Entity\Payment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Payment>
 */
class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    /**
     * Find pending payments
     */
    public function findPending(): array
    {
        return $this->findBy(['status' => 'pending'], ['dueDate' => 'ASC']);
    }

    /**
     * Find overdue payments
     */
    public function findOverdue(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.status = :status')
            ->andWhere('p.dueDate < :today')
            ->setParameter('status', 'pending')
            ->setParameter('today', new \DateTime())
            ->orderBy('p.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get debtors report by grade
     */
    public function getDebtorsReport(): array
    {
        return $this->createQueryBuilder('p')
            ->select('g.name as gradeName, s.id as studentId, u.firstName, u.lastName, SUM(p.amount) as totalDebt')
            ->innerJoin('p.enrollment', 'e')
            ->innerJoin('e.student', 's')
            ->innerJoin('s.user', 'u')
            ->innerJoin('e.grade', 'g')
            ->where('p.status = :status')
            ->setParameter('status', 'pending')
            ->groupBy('g.id, s.id, u.firstName, u.lastName')
            ->orderBy('g.name', 'ASC')
            ->addOrderBy('u.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get daily collection total
     */
    public function getDailyTotal(\DateTime $date): float
    {
        $result = $this->createQueryBuilder('p')
            ->select('SUM(p.amount) as total')
            ->where('DATE(p.paidDate) = :date')
            ->andWhere('p.status = :status')
            ->setParameter('date', $date->format('Y-m-d'))
            ->setParameter('status', 'paid')
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }
}
