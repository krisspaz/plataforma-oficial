<?php

declare(strict_types=1);

namespace App\Application\Academic\Query;

final class GetSubjectByCodeQuery
{
    public function __construct(
        public readonly string $code
    ) {}
}
