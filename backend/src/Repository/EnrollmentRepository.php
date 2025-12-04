<?php

namespace App\Repository;

use App\Entity\Enrollment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Enrollment>
 */
class EnrollmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Enrollment::class);
    }

    /**
     * Find active enrollments by academic year
     */
    public function findActiveByYear(int $academicYear): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.academicYear = :year')
            ->andWhere('e.status = :status')
            ->setParameter('year', $academicYear)
            ->setParameter('status', 'active')
            ->orderBy('e.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find enrollments by student
     */
    public function findByStudent(int $studentId): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.student = :studentId')
            ->setParameter('studentId', $studentId)
            ->orderBy('e.academicYear', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get enrollment statistics by grade
     */
    public function getStatsByGrade(int $academicYear): array
    {
        return $this->createQueryBuilder('e')
            ->select('g.name as gradeName, COUNT(e.id) as total')
            ->innerJoin('e.grade', 'g')
            ->where('e.academicYear = :year')
            ->andWhere('e.status = :status')
            ->setParameter('year', $academicYear)
            ->setParameter('status', 'active')
            ->groupBy('g.id')
            ->orderBy('g.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count active enrollments by year
     */
    public function countActiveByYear(int $academicYear): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.academicYear = :year')
            ->andWhere('e.status = :status')
            ->setParameter('year', $academicYear)
            ->setParameter('status', 'active')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find recent active enrollments by year
     */
    public function findRecentActiveByYear(int $academicYear, int $limit = 10): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.academicYear = :year')
            ->andWhere('e.status = :status')
            ->setParameter('year', $academicYear)
            ->setParameter('status', 'active')
            ->orderBy('e.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
