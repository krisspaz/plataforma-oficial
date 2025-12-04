<?php

declare(strict_types=1);

namespace App\Application\Payment\DTO;

use App\Domain\Payment\Entity\PaymentPlan;

/**
 * Data Transfer Object for Payment Plans.
 */
final class PaymentPlanDTO
{
    public function __construct(
        public readonly string $id,
        public readonly int $enrollmentId,
        public readonly string $studentName,
        public readonly string $grade,
        public readonly float $totalAmount,
        public readonly int $numberOfInstallments,
        public readonly float $installmentAmount,
        public readonly int $dayOfMonth,
        public readonly string $status,
        public readonly float $totalPaid,
        public readonly float $totalPending,
        public readonly float $progress,
        public readonly bool $hasOverdue,
        public readonly int $paidInstallments,
        public readonly int $pendingInstallments,
        public readonly string $createdAt,
        public readonly ?string $completedAt,
        public readonly array $installments
    ) {}

    public static function fromEntity(PaymentPlan $plan): self
    {
        $enrollment = $plan->getEnrollment();
        $student = $enrollment->getStudent();

        $installmentDTOs = [];
        foreach ($plan->getInstallments() as $installment) {
            $installmentDTOs[] = InstallmentDTO::fromEntity($installment);
        }

        return new self(
            id: (string) $plan->getId(),
            enrollmentId: $enrollment->getId(),
            studentName: sprintf('%s %s', $student->getFirstName(), $student->getLastName()),
            grade: sprintf('%s - %s', $enrollment->getGrade()->getName(), $enrollment->getSection()->getName()),
            totalAmount: $plan->getTotalAmount()->getValue(),
            numberOfInstallments: $plan->getNumberOfInstallments(),
            installmentAmount: $plan->getInstallmentAmount()->getValue(),
            dayOfMonth: $plan->getDayOfMonth(),
            status: $plan->getStatus(),
            totalPaid: $plan->getTotalPaid()->getValue(),
            totalPending: $plan->getTotalPending()->getValue(),
            progress: $plan->getProgress(),
            hasOverdue: $plan->hasOverdueInstallments(),
            paidInstallments: $plan->getPaidInstallments()->count(),
            pendingInstallments: $plan->getPendingInstallments()->count(),
            createdAt: $plan->getCreatedAt()->format('Y-m-d H:i:s'),
            completedAt: $plan->getCompletedAt()?->format('Y-m-d H:i:s'),
            installments: $installmentDTOs
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'enrollment_id' => $this->enrollmentId,
            'student_name' => $this->studentName,
            'grade' => $this->grade,
            'total_amount' => $this->totalAmount,
            'number_of_installments' => $this->numberOfInstallments,
            'installment_amount' => $this->installmentAmount,
            'day_of_month' => $this->dayOfMonth,
            'status' => $this->status,
            'total_paid' => $this->totalPaid,
            'total_pending' => $this->totalPending,
            'progress' => $this->progress,
            'has_overdue' => $this->hasOverdue,
            'paid_installments' => $this->paidInstallments,
            'pending_installments' => $this->pendingInstallments,
            'created_at' => $this->createdAt,
            'completed_at' => $this->completedAt,
            'installments' => array_map(fn($i) => $i->toArray(), $this->installments),
        ];
    }
}
