<?php

declare(strict_types=1);

namespace App\Application\Academic\Query;

final class GetAvailableSectionsQuery
{
    public function __construct(
        public readonly ?int $year = null
    ) {}
}
