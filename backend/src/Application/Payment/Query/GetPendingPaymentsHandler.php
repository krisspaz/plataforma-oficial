<?php

declare(strict_types=1);

namespace App\Application\Payment\Query;

use App\Repository\PaymentRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetPendingPaymentsHandler
{
    public function __construct(
        private readonly PaymentRepository $paymentRepository
    ) {}

    public function __invoke(GetPendingPaymentsQuery $query): array
    {
        return $this->paymentRepository->findPending();
    }
}
