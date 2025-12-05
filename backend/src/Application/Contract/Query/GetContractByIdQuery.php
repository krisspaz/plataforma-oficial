<?php

declare(strict_types=1);

namespace App\Application\Contract\Query;

final class GetContractByIdQuery
{
    public function __construct(
        public readonly int $id
    ) {}
}
