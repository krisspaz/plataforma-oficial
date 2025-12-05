<?php

declare(strict_types=1);

namespace App\Application\Academic\Query;

final class GetGradesByLevelQuery
{
    public function __construct(
        public readonly string $level
    ) {}
}
