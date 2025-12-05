<?php

declare(strict_types=1);

namespace App\Application\Parent\Query;

/**
 * Query to get a parent by ID
 */
final class GetParentByIdQuery
{
    public function __construct(
        public readonly int $id
    ) {}
}
