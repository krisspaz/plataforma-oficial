<?php

declare(strict_types=1);

namespace App\Application\Contract\Query;

final class GetContractsByStudentQuery
{
    public function __construct(
        public readonly int $studentId
    ) {}
}
