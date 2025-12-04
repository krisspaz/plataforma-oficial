<?php

declare(strict_types=1);

namespace App\Application\Payment\Query;

use App\Application\Payment\DTO\DebtorDTO;
use App\Domain\Payment\Repository\InstallmentRepositoryInterface;
use App\Infrastructure\Cache\CacheService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetDebtorsHandler
{
    public function __construct(
        private readonly InstallmentRepositoryInterface $installmentRepository,
        private readonly CacheService $cache
    ) {}

    public function __invoke(GetDebtorsQuery $query): array
    {
        return $this->cache->getDebtorsReport(
            $query->gradeId,
            function () use ($query): array {
                $overdue = $this->installmentRepository->findOverdue($query->gradeId, $query->minDebt);

                // Group by student
                $byStudent = [];
                foreach ($overdue as $installment) {
                    $studentId = $installment->getPaymentPlan()->getEnrollment()->getStudent()->getId();

                    if (!isset($byStudent[$studentId])) {
                        $byStudent[$studentId] = [
                            'student' => $installment->getPaymentPlan()->getEnrollment()->getStudent(),
                            'enrollment' => $installment->getPaymentPlan()->getEnrollment(),
                            'installments' => [],
                            'total_debt' => 0.0,
                        ];
                    }

                    $byStudent[$studentId]['installments'][] = $installment;
                    $byStudent[$studentId]['total_debt'] += $installment->getPendingAmount();
                }

                return array_map(
                    fn($data) => DebtorDTO::create($data),
                    array_values($byStudent)
                );
            }
        );
    }
}
