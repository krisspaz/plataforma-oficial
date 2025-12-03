<?php

namespace App\Repository;

use App\Entity\Subject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Subject>
 */
class SubjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subject::class);
    }

    /**
     * Find subject by code
     */
    public function findOneByCode(string $code): ?Subject
    {
        return $this->findOneBy(['code' => $code]);
    }

    /**
     * Search subjects by name or code
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('s')
            ->where('s.name LIKE :query')
            ->orWhere('s.code LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('s.code', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
