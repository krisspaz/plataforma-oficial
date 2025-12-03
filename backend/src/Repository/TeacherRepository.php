<?php

namespace App\Repository;

use App\Entity\Teacher;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Teacher>
 */
class TeacherRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Teacher::class);
    }

    /**
     * Find teachers by specialization
     */
    public function findBySpecialization(string $specialization): array
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.user', 'u')
            ->where('t.specialization = :specialization')
            ->andWhere('u.isActive = :active')
            ->setParameter('specialization', $specialization)
            ->setParameter('active', true)
            ->orderBy('u.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find teachers with birthdays in current month
     */
    public function findBirthdaysThisMonth(): array
    {
        $currentMonth = (int) date('m');
        
        return $this->createQueryBuilder('t')
            ->innerJoin('t.user', 'u')
            ->where('EXTRACT(MONTH FROM t.birthDate) = :month')
            ->andWhere('u.isActive = :active')
            ->setParameter('month', $currentMonth)
            ->setParameter('active', true)
            ->orderBy('EXTRACT(DAY FROM t.birthDate)', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Search teachers by name
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('t')
            ->innerJoin('t.user', 'u')
            ->where('u.firstName LIKE :query')
            ->orWhere('u.lastName LIKE :query')
            ->orWhere('t.specialization LIKE :query')
            ->andWhere('u.isActive = :active')
            ->setParameter('query', '%' . $query . '%')
            ->setParameter('active', true)
            ->orderBy('u.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
