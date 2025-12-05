<?php

declare(strict_types=1);

namespace App\Application\Academic\Query;

final class GetSubjectByIdQuery
{
    public function __construct(
        public readonly int $id
    ) {}
}
