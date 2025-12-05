<?php

declare(strict_types=1);

namespace App\Application\Payment\Query;

use App\Repository\PaymentRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetDailyTotalHandler
{
    public function __construct(
        private readonly PaymentRepository $paymentRepository
    ) {}

    public function __invoke(GetDailyTotalQuery $query): array
    {
        $date = $query->date ?? new \DateTime();
        $total = $this->paymentRepository->getDailyTotal($date);

        return [
            'date' => $date->format('Y-m-d'),
            'total' => $total
        ];
    }
}
