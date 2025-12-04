<?php

declare(strict_types=1);

namespace App\Application\Payment\Query;

use DateTimeImmutable;

/**
 * Query to get daily closure report.
 */
final class GetDailyClosureQuery
{
    public function __construct(
        public readonly ?string $date = null // Y-m-d format, null = today
    ) {}

    public function getDate(): DateTimeImmutable
    {
        if ($this->date === null) {
            return new DateTimeImmutable('today');
        }

        $parsed = DateTimeImmutable::createFromFormat('Y-m-d', $this->date);

        if ($parsed === false) {
            throw new \InvalidArgumentException('Invalid date format. Expected Y-m-d');
        }

        return $parsed;
    }
}
