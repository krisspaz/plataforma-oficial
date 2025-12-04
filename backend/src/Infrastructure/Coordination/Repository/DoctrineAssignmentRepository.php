<?php

declare(strict_types=1);

namespace App\Infrastructure\Coordination\Repository;

use App\Domain\Coordination\Entity\Assignment;
use App\Domain\Coordination\Repository\AssignmentRepositoryInterface;
use App\Entity\Grade;
use App\Entity\Section;
use App\Entity\Teacher;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Assignment>
 */
class DoctrineAssignmentRepository extends ServiceEntityRepository implements AssignmentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Assignment::class);
    }

    public function save(Assignment $assignment): void
    {
        $em = $this->getEntityManager();
        $em->persist($assignment);
        $em->flush();
    }

    public function remove(Assignment $assignment): void
    {
        $em = $this->getEntityManager();
        $em->remove($assignment);
        $em->flush();
    }

    public function findByTeacher(Teacher $teacher, int $academicYear): array
    {
        return $this->findBy([
            'teacher' => $teacher,
            'academicYear' => $academicYear
        ]);
    }

    public function findByGradeAndSection(Grade $grade, Section $section, int $academicYear): array
    {
        return $this->findBy([
            'grade' => $grade,
            'section' => $section,
            'academicYear' => $academicYear
        ]);
    }

    public function findActiveByTeacher(Teacher $teacher): array
    {
        return $this->findBy([
            'teacher' => $teacher,
            'isActive' => true
        ]);
    }
}
