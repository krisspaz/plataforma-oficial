<?php

declare(strict_types=1);

namespace App\Infrastructure\Payment\Repository;

use App\Domain\Payment\Entity\PaymentPlan;
use App\Domain\Payment\Repository\PaymentPlanRepositoryInterface;
use App\Entity\Enrollment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<PaymentPlan>
 */
class DoctrinePaymentPlanRepository extends ServiceEntityRepository implements PaymentPlanRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentPlan::class);
    }

    public function save(PaymentPlan $paymentPlan): void
    {
        $em = $this->getEntityManager();
        $em->persist($paymentPlan);
        $em->flush();
    }

    public function findById(Uuid $id): ?PaymentPlan
    {
        return $this->find($id);
    }

    public function findByEnrollment(Enrollment $enrollment): ?PaymentPlan
    {
        return $this->findOneBy(['enrollment' => $enrollment]);
    }

    public function findActiveByEnrollment(Enrollment $enrollment): ?PaymentPlan
    {
        return $this->findOneBy([
            'enrollment' => $enrollment,
            'status' => 'active'
        ]);
    }

    /**
     * @return PaymentPlan[]
     */
    public function findWithOverdueInstallments(): array
    {
        $qb = $this->createQueryBuilder('pp')
            ->join('pp.installments', 'i')
            ->where('i.status = :status')
            ->andWhere('i.dueDate < :today')
            ->andWhere('pp.status = :planStatus')
            ->setParameter('status', 'pending')
            ->setParameter('today', new \DateTimeImmutable('today'))
            ->setParameter('planStatus', 'active')
            ->groupBy('pp.id');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return PaymentPlan[]
     */
    public function findByAcademicYear(int $year): array
    {
        $qb = $this->createQueryBuilder('pp')
            ->join('pp.enrollment', 'e')
            ->where('e.academicYear = :year')
            ->setParameter('year', $year)
            ->orderBy('pp.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function remove(PaymentPlan $paymentPlan): void
    {
        $em = $this->getEntityManager();
        $em->remove($paymentPlan);
        $em->flush();
    }
}
