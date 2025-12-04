<?php

declare(strict_types=1);

namespace App\Infrastructure\Grades\Repository;

use App\Domain\Grades\Entity\GradeRecord;
use App\Domain\Grades\Repository\GradeRecordRepositoryInterface;
use App\Entity\Student;
use App\Entity\Subject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineGradeRecordRepository extends ServiceEntityRepository implements GradeRecordRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GradeRecord::class);
    }

    public function save(GradeRecord $record): void
    {
        $em = $this->getEntityManager();
        $em->persist($record);
        $em->flush();
    }

    public function findByStudentAndBimester(Student $student, int $bimester, int $academicYear): array
    {
        return $this->findBy([
            'student' => $student,
            'bimester' => $bimester,
            'academicYear' => $academicYear,
        ]);
    }

    public function findBySubjectAndBimester(Subject $subject, int $bimester, int $academicYear): array
    {
        return $this->findBy([
            'subject' => $subject,
            'bimester' => $bimester,
            'academicYear' => $academicYear,
        ]);
    }

    public function findOne(Student $student, Subject $subject, int $bimester, int $academicYear): ?GradeRecord
    {
        return $this->findOneBy([
            'student' => $student,
            'subject' => $subject,
            'bimester' => $bimester,
            'academicYear' => $academicYear,
        ]);
    }

    public function getStudentAverage(Student $student, int $academicYear): float
    {
        $qb = $this->createQueryBuilder('g')
            ->select('AVG(g.grade) as avg')
            ->where('g.student = :student')
            ->andWhere('g.academicYear = :year')
            ->setParameter('student', $student)
            ->setParameter('year', $academicYear);

        $result = $qb->getQuery()->getSingleScalarResult();
        return round((float) ($result ?? 0), 2);
    }

    public function getSubjectAverage(Subject $subject, int $bimester, int $academicYear): float
    {
        $qb = $this->createQueryBuilder('g')
            ->select('AVG(g.grade) as avg')
            ->where('g.subject = :subject')
            ->andWhere('g.bimester = :bimester')
            ->andWhere('g.academicYear = :year')
            ->setParameter('subject', $subject)
            ->setParameter('bimester', $bimester)
            ->setParameter('year', $academicYear);

        $result = $qb->getQuery()->getSingleScalarResult();
        return round((float) ($result ?? 0), 2);
    }
}
