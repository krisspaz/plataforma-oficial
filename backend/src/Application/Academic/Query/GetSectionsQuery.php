<?php

declare(strict_types=1);

namespace App\Application\Academic\Query;

final class GetSectionsQuery
{
    public function __construct(
        public readonly ?int $gradeId = null,
        public readonly ?int $year = null
    ) {}
}
