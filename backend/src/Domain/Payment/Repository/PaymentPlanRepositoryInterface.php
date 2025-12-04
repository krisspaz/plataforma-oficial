<?php

declare(strict_types=1);

namespace App\Domain\Payment\Repository;

use App\Domain\Payment\Entity\PaymentPlan;
use App\Entity\Enrollment;
use Symfony\Component\Uid\Uuid;

interface PaymentPlanRepositoryInterface
{
    public function save(PaymentPlan $paymentPlan): void;

    public function findById(Uuid $id): ?PaymentPlan;

    public function findByEnrollment(Enrollment $enrollment): ?PaymentPlan;

    public function findActiveByEnrollment(Enrollment $enrollment): ?PaymentPlan;

    /**
     * @return PaymentPlan[]
     */
    public function findWithOverdueInstallments(): array;

    /**
     * @return PaymentPlan[]
     */
    public function findByAcademicYear(int $year): array;

    public function remove(PaymentPlan $paymentPlan): void;
}
