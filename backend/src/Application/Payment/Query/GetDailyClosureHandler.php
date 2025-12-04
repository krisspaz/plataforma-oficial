<?php

declare(strict_types=1);

namespace App\Application\Payment\Query;

use App\Domain\Payment\Service\DebtorReportGenerator;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetDailyClosureHandler
{
    public function __construct(
        private readonly DebtorReportGenerator $reportGenerator
    ) {}

    public function __invoke(GetDailyClosureQuery $query): array
    {
        return $this->reportGenerator->generateDailyClosure($query->getDate());
    }
}
