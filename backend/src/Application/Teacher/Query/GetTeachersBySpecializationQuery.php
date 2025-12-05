<?php

declare(strict_types=1);

namespace App\Application\Teacher\Query;

/**
 * Query to get teachers by specialization
 */
final class GetTeachersBySpecializationQuery
{
    public function __construct(
        public readonly string $specialization
    ) {}
}
