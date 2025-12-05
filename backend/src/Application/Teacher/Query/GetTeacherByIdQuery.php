<?php

declare(strict_types=1);

namespace App\Application\Teacher\Query;

/**
 * Query to get a teacher by ID
 */
final class GetTeacherByIdQuery
{
    public function __construct(
        public readonly int $id
    ) {}
}
