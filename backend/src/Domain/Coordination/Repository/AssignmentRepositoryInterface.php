<?php

declare(strict_types=1);

namespace App\Domain\Coordination\Repository;

use App\Domain\Coordination\Entity\Assignment;
use App\Entity\Grade;
use App\Entity\Section;
use App\Entity\Teacher;

interface AssignmentRepositoryInterface
{
    public function save(Assignment $assignment): void;
    public function remove(Assignment $assignment): void;
    public function findByTeacher(Teacher $teacher, int $academicYear): array;
    public function findByGradeAndSection(Grade $grade, Section $section, int $academicYear): array;
    public function findActiveByTeacher(Teacher $teacher): array;
}
