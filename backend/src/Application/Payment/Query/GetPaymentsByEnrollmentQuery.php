<?php

declare(strict_types=1);

namespace App\Application\Payment\Query;

/**
 * Query to get payments by enrollment ID
 */
final class GetPaymentsByEnrollmentQuery
{
    public function __construct(
        public readonly int $enrollmentId
    ) {}
}
