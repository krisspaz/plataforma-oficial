<?php

declare(strict_types=1);

namespace App\Infrastructure\Coordination\Repository;

use App\Domain\Coordination\Entity\CalendarEvent;
use App\Domain\Coordination\Repository\CalendarEventRepositoryInterface;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CalendarEvent>
 */
class DoctrineCalendarEventRepository extends ServiceEntityRepository implements CalendarEventRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CalendarEvent::class);
    }

    public function save(CalendarEvent $event): void
    {
        $em = $this->getEntityManager();
        $em->persist($event);
        $em->flush();
    }

    public function remove(CalendarEvent $event): void
    {
        $em = $this->getEntityManager();
        $em->remove($event);
        $em->flush();
    }

    public function findBetween(DateTimeImmutable $start, DateTimeImmutable $end): array
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.startDate >= :start')
            ->andWhere('e.endDate <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('e.startDate', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function findUpcoming(int $limit = 5): array
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.startDate >= :now')
            ->setParameter('now', new DateTimeImmutable())
            ->orderBy('e.startDate', 'ASC')
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }
}
