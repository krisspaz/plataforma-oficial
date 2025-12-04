<?php

declare(strict_types=1);

namespace App\Domain\Grades\Repository;

use App\Domain\Grades\Entity\BimesterClosure;
use App\Entity\Grade;

interface BimesterClosureRepositoryInterface
{
    public function save(BimesterClosure $closure): void;

    public function find(Grade $grade, int $bimester, int $academicYear): ?BimesterClosure;

    public function findByGrade(Grade $grade, int $academicYear): array;

    public function isClosed(Grade $grade, int $bimester, int $academicYear): bool;
}
