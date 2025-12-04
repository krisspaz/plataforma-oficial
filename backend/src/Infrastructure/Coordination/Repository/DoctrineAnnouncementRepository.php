<?php

declare(strict_types=1);

namespace App\Infrastructure\Coordination\Repository;

use App\Domain\Coordination\Entity\Announcement;
use App\Domain\Coordination\Repository\AnnouncementRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Announcement>
 */
class DoctrineAnnouncementRepository extends ServiceEntityRepository implements AnnouncementRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Announcement::class);
    }

    public function save(Announcement $announcement): void
    {
        $em = $this->getEntityManager();
        $em->persist($announcement);
        $em->flush();
    }

    public function remove(Announcement $announcement): void
    {
        $em = $this->getEntityManager();
        $em->remove($announcement);
        $em->flush();
    }

    public function findActive(): array
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.isActive = :active')
            ->andWhere('a.expiresAt IS NULL OR a.expiresAt > :now')
            ->setParameter('active', true)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('a.createdAt', 'DESC');

        return $qb->getQuery()->getResult();
    }

    public function findByType(string $type): array
    {
        return $this->findBy(
            ['type' => $type, 'isActive' => true],
            ['createdAt' => 'DESC']
        );
    }
}
