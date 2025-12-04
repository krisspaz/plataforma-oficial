<?php

declare(strict_types=1);

namespace App\Application\Grades\Command;

final class CloseBimesterCommand
{
    public function __construct(
        public readonly int $gradeId,
        public readonly int $bimester,
        public readonly int $academicYear,
        public readonly int $userId
    ) {}
}
