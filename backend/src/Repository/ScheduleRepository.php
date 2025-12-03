<?php

namespace App\Repository;

use App\Entity\Schedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Schedule>
 */
class ScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Schedule::class);
    }

    /**
     * Find schedules by section
     */
    public function findBySection(int $sectionId, int $academicYear): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.section = :sectionId')
            ->andWhere('s.academicYear = :year')
            ->setParameter('sectionId', $sectionId)
            ->setParameter('year', $academicYear)
            ->orderBy('s.dayOfWeek', 'ASC')
            ->addOrderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find schedules by teacher
     */
    public function findByTeacher(int $teacherId, int $academicYear): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.teacher = :teacherId')
            ->andWhere('s.academicYear = :year')
            ->setParameter('teacherId', $teacherId)
            ->setParameter('year', $academicYear)
            ->orderBy('s.dayOfWeek', 'ASC')
            ->addOrderBy('s.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Check for conflicts
     */
    public function hasConflict(int $teacherId, int $dayOfWeek, \DateTimeInterface $startTime, \DateTimeInterface $endTime, int $academicYear, ?int $excludeId = null): bool
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.teacher = :teacherId')
            ->andWhere('s.dayOfWeek = :dayOfWeek')
            ->andWhere('s.academicYear = :year')
            ->andWhere('(
                (s.startTime < :endTime AND s.endTime > :startTime)
            )')
            ->setParameter('teacherId', $teacherId)
            ->setParameter('dayOfWeek', $dayOfWeek)
            ->setParameter('startTime', $startTime)
            ->setParameter('endTime', $endTime)
            ->setParameter('year', $academicYear);

        if ($excludeId) {
            $qb->andWhere('s.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        return count($qb->getQuery()->getResult()) > 0;
    }
}
