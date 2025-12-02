<?php

namespace App\Repository;

use App\Entity\Grade;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Grade>
 */
class GradeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Grade::class);
    }

    /**
     * Find grades by level
     */
    public function findByLevel(string $level): array
    {
        return $this->findBy(['level' => $level], ['name' => 'ASC']);
    }

    /**
     * Get all grades with section count
     */
    public function findAllWithSectionCount(): array
    {
        return $this->createQueryBuilder('g')
            ->select('g, COUNT(s.id) as sectionCount')
            ->leftJoin('g.sections', 's')
            ->groupBy('g.id')
            ->orderBy('g.level', 'ASC')
            ->addOrderBy('g.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
