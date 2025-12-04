<?php

declare(strict_types=1);

namespace App\Application\Payment\Command;

use App\Domain\Payment\Repository\InstallmentRepositoryInterface;
use App\Infrastructure\Cache\CacheService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RecordInstallmentPaymentHandler
{
    public function __construct(
        private readonly InstallmentRepositoryInterface $repository,
        private readonly CacheService $cache
    ) {}

    public function __invoke(RecordInstallmentPaymentCommand $command): void
    {
        $installment = $this->repository->find($command->installmentId);

        if (!$installment) {
            throw new \InvalidArgumentException('Installment not found');
        }

        $installment->recordPayment(
            $command->amount,
            $command->paymentMethod,
            $command->reference
        );

        $this->repository->save($installment);

        // Invalidate payment caches
        $this->cache->invalidatePayments();
    }
}
