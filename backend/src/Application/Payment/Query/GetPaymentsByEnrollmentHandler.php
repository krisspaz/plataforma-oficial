<?php

declare(strict_types=1);

namespace App\Application\Payment\Query;

use App\Repository\EnrollmentRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetPaymentsByEnrollmentHandler
{
    public function __construct(
        private readonly EnrollmentRepository $enrollmentRepository
    ) {}

    public function __invoke(GetPaymentsByEnrollmentQuery $query): ?array
    {
        $enrollment = $this->enrollmentRepository->find($query->enrollmentId);

        if (!$enrollment) {
            return null;
        }

        return [
            'payments' => $enrollment->getPayments(),
            'totalPaid' => $enrollment->getTotalPaid(),
            'totalPending' => $enrollment->getTotalPending(),
            'enrollment' => $enrollment
        ];
    }
}
