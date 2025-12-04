<?php

declare(strict_types=1);

namespace App\Application\Coordination\Query;

final class GetAnnouncementsQuery
{
    public function __construct(
        public readonly ?string $type = null
    ) {}
}
