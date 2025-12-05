<?php

declare(strict_types=1);

namespace App\Infrastructure\Grades\Repository;

use App\Domain\Grades\Entity\BimesterClosure;
use App\Domain\Grades\Repository\BimesterClosureRepositoryInterface;
use App\Entity\Grade;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineBimesterClosureRepository extends ServiceEntityRepository implements BimesterClosureRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BimesterClosure::class);
    }

    public function save(BimesterClosure $closure): void
    {
        $em = $this->getEntityManager();
        $em->persist($closure);
        $em->flush();
    }

    public function findByCriteria(Grade $grade, int $bimester, int $academicYear): ?BimesterClosure
    {
        return $this->findOneBy([
            'grade' => $grade,
            'bimester' => $bimester,
            'academicYear' => $academicYear,
        ]);
    }

    public function findByGrade(Grade $grade, int $academicYear): array
    {
        return $this->findBy([
            'grade' => $grade,
            'academicYear' => $academicYear,
        ], ['bimester' => 'ASC']);
    }

    public function isClosed(Grade $grade, int $bimester, int $academicYear): bool
    {
        $closure = $this->findByCriteria($grade, $bimester, $academicYear);
        return $closure !== null && $closure->isClosed();
    }
}
