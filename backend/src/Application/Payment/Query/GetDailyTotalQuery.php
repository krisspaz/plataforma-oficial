<?php

declare(strict_types=1);

namespace App\Application\Payment\Query;

/**
 * Query to get daily payment total
 */
final class GetDailyTotalQuery
{
    public function __construct(
        public readonly ?\DateTime $date = null
    ) {}
}
