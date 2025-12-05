<?php

declare(strict_types=1);

namespace App\Application\Academic\Query;

final class GetSectionByIdQuery
{
    public function __construct(
        public readonly int $id
    ) {}
}
