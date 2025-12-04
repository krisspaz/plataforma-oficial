<?php

declare(strict_types=1);

namespace App\Domain\Grades\Repository;

use App\Domain\Grades\Entity\GradeRecord;
use App\Entity\Student;
use App\Entity\Subject;

interface GradeRecordRepositoryInterface
{
    public function save(GradeRecord $record): void;

    public function findByStudentAndBimester(Student $student, int $bimester, int $academicYear): array;

    public function findBySubjectAndBimester(Subject $subject, int $bimester, int $academicYear): array;

    public function findOne(Student $student, Subject $subject, int $bimester, int $academicYear): ?GradeRecord;

    public function getStudentAverage(Student $student, int $academicYear): float;

    public function getSubjectAverage(Subject $subject, int $bimester, int $academicYear): float;
}
