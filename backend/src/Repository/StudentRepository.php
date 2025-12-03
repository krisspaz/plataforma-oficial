<?php

namespace App\Repository;

use App\Entity\Student;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Student>
 */
class StudentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Student::class);
    }

    /**
     * Find students by grade
     */
    public function findByGrade(int $gradeId): array
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.enrollments', 'e')
            ->where('e.grade = :gradeId')
            ->andWhere('e.status = :status')
            ->setParameter('gradeId', $gradeId)
            ->setParameter('status', 'active')
            ->orderBy('s.user.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find students by section
     */
    public function findBySection(int $sectionId): array
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.enrollments', 'e')
            ->where('e.section = :sectionId')
            ->andWhere('e.status = :status')
            ->setParameter('sectionId', $sectionId)
            ->setParameter('status', 'active')
            ->orderBy('s.user.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search students by name or personal ID
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('s')
            ->innerJoin('s.user', 'u')
            ->where('u.firstName LIKE :query')
            ->orWhere('u.lastName LIKE :query')
            ->orWhere('s.personalId LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('u.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get statistics by gender
     */
    public function getStatsByGender(): array
    {
        return $this->createQueryBuilder('s')
            ->select('s.gender, COUNT(s.id) as total')
            ->groupBy('s.gender')
            ->getQuery()
            ->getResult();
    }
}
