<?php

declare(strict_types=1);

namespace App\Application\Payment\Query;

use App\Application\Payment\DTO\DailyClosureDTO;
use App\Domain\Payment\Repository\InstallmentRepositoryInterface;
use App\Infrastructure\Cache\CacheService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetDailyClosureHandler
{
    public function __construct(
        private readonly InstallmentRepositoryInterface $installmentRepository,
        private readonly CacheService $cache
    ) {}

    public function __invoke(GetDailyClosureQuery $query): DailyClosureDTO
    {
        $dateStr = $query->date->format('Y-m-d');

        $data = $this->cache->getDailyClosure(
            $dateStr,
            function () use ($query): array {
                $payments = $this->installmentRepository->findPaymentsByDate($query->date);

                $totalAmount = 0.0;
                $byMethod = [];

                foreach ($payments as $payment) {
                    $amount = $payment->getPaidAmount();
                    $method = $payment->getPaymentMethod();

                    $totalAmount += $amount;
                    $byMethod[$method] = ($byMethod[$method] ?? 0) + $amount;
                }

                return [
                    'date' => $query->date->format('Y-m-d'),
                    'total_amount' => $totalAmount,
                    'payment_count' => count($payments),
                    'by_method' => $byMethod,
                ];
            }
        );

        return DailyClosureDTO::fromArray($data);
    }
}
