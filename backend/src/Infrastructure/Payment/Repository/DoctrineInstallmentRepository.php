<?php

declare(strict_types=1);

namespace App\Infrastructure\Payment\Repository;

use App\Domain\Payment\Entity\Installment;
use App\Domain\Payment\Repository\InstallmentRepositoryInterface;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<Installment>
 */
class DoctrineInstallmentRepository extends ServiceEntityRepository implements InstallmentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Installment::class);
    }

    public function save(Installment $installment): void
    {
        $em = $this->getEntityManager();
        $em->persist($installment);
        $em->flush();
    }

    public function findById(Uuid $id): ?Installment
    {
        return $this->find($id);
    }

    /**
     * @return Installment[]
     */
    public function findOverdue(): array
    {
        $qb = $this->createQueryBuilder('i')
            ->join('i.paymentPlan', 'pp')
            ->where('i.status = :status')
            ->andWhere('i.dueDate < :today')
            ->andWhere('pp.status = :planStatus')
            ->setParameter('status', 'pending')
            ->setParameter('today', new \DateTimeImmutable('today'))
            ->setParameter('planStatus', 'active')
            ->orderBy('i.dueDate', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Installment[]
     */
    public function findDueToday(): array
    {
        $today = new \DateTimeImmutable('today');

        $qb = $this->createQueryBuilder('i')
            ->where('i.status = :status')
            ->andWhere('i.dueDate = :today')
            ->setParameter('status', 'pending')
            ->setParameter('today', $today)
            ->orderBy('i.number', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Installment[]
     */
    public function findDueBetween(DateTimeInterface $start, DateTimeInterface $end): array
    {
        $qb = $this->createQueryBuilder('i')
            ->where('i.status = :status')
            ->andWhere('i.dueDate >= :start')
            ->andWhere('i.dueDate <= :end')
            ->setParameter('status', 'pending')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('i.dueDate', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Installment[]
     */
    public function findPaidOnDate(DateTimeInterface $date): array
    {
        $startOfDay = \DateTimeImmutable::createFromInterface($date)->setTime(0, 0, 0);
        $endOfDay = \DateTimeImmutable::createFromInterface($date)->setTime(23, 59, 59);

        $qb = $this->createQueryBuilder('i')
            ->where('i.status = :status')
            ->andWhere('i.paidAt >= :start')
            ->andWhere('i.paidAt <= :end')
            ->setParameter('status', 'paid')
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->orderBy('i.paidAt', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Get summary statistics for a date range.
     */
    public function getStatistics(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $result = $this->createQueryBuilder('i')
            ->select('COUNT(i.id) as total_count')
            ->addSelect('SUM(i.amount) as total_amount')
            ->where('i.status = :status')
            ->andWhere('i.paidAt >= :start')
            ->andWhere('i.paidAt <= :end')
            ->setParameter('status', 'paid')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleResult();

        return [
            'count' => (int) $result['total_count'],
            'amount' => (float) ($result['total_amount'] ?? 0),
        ];
    }
}
