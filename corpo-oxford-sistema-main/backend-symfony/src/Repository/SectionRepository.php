<?php

namespace App\Repository;

use App\Entity\Section;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Section>
 */
class SectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Section::class);
    }

    /**
     * Find sections by grade and academic year
     */
    public function findByGradeAndYear(int $gradeId, int $academicYear): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.grade = :gradeId')
            ->andWhere('s.academicYear = :year')
            ->setParameter('gradeId', $gradeId)
            ->setParameter('year', $academicYear)
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find sections with available space
     */
    public function findWithAvailableSpace(int $academicYear): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.enrollments', 'e', 'WITH', 'e.status = :status')
            ->where('s.academicYear = :year')
            ->groupBy('s.id')
            ->having('COUNT(e.id) < s.capacity OR s.capacity IS NULL')
            ->setParameter('year', $academicYear)
            ->setParameter('status', 'active')
            ->orderBy('s.grade', 'ASC')
            ->addOrderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
