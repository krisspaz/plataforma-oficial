<?php

declare(strict_types=1);

namespace App\Application\Contract\Query;

final class GetContractsByEnrollmentQuery
{
    public function __construct(
        public readonly int $enrollmentId
    ) {}
}
