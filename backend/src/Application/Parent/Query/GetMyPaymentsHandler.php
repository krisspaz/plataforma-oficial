<?php

declare(strict_types=1);

namespace App\Application\Parent\Query;

use App\Repository\ParentRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetMyPaymentsHandler
{
    public function __construct(
        private readonly ParentRepository $parentRepository
    ) {}

    public function __invoke(GetMyPaymentsQuery $query): ?array
    {
        $parent = $this->parentRepository->findOneBy(['user' => $query->user]);
        if (!$parent) {
            return null;
        }

        $allPayments = [];
        $totalPending = 0;
        $totalPaid = 0;

        foreach ($parent->getStudents() as $student) {
            foreach ($student->getEnrollments() as $enrollment) {
                if ($enrollment->getStatus() === 'active') {
                    $payments = $enrollment->getPayments();
                    foreach ($payments as $payment) {
                        $allPayments[] = $payment;
                        if ($payment->getStatus() === 'pending') {
                            $totalPending += $payment->getAmount();
                        } else {
                            $totalPaid += $payment->getAmount();
                        }
                    }
                }
            }
        }

        return [
            'payments' => $allPayments,
            'summary' => [
                'total_pending' => $totalPending,
                'total_paid' => $totalPaid,
                'count' => count($allPayments)
            ]
        ];
    }
}
