<?php

declare(strict_types=1);

namespace App\Application\Payment\Query;

use App\Repository\PaymentRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetPaymentsHandler
{
    public function __construct(
        private readonly PaymentRepository $paymentRepository
    ) {}

    public function __invoke(GetPaymentsQuery $query): array
    {
        $page = max(1, $query->page);
        $limit = min(100, max(1, $query->limit));
        $offset = ($page - 1) * $limit;

        if ($query->status) {
            $payments = $this->paymentRepository->findBy(
                ['status' => $query->status],
                ['createdAt' => 'DESC'],
                $limit,
                $offset
            );
            $total = $this->paymentRepository->count(['status' => $query->status]);
        } else {
            $payments = $this->paymentRepository->findBy(
                [],
                ['createdAt' => 'DESC'],
                $limit,
                $offset
            );
            $total = $this->paymentRepository->count([]);
        }

        return [
            'payments' => $payments,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ];
    }
}
