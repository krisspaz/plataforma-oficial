<?php

declare(strict_types=1);

namespace App\Application\Payment\DTO;

use App\Domain\Payment\Entity\Installment;

/**
 * Data Transfer Object for Installments.
 */
final class InstallmentDTO
{
    public function __construct(
        public readonly string $id,
        public readonly int $number,
        public readonly int $total,
        public readonly string $formattedNumber,
        public readonly float $amount,
        public readonly string $dueDate,
        public readonly string $status,
        public readonly bool $isPaid,
        public readonly bool $isOverdue,
        public readonly int $daysOverdue,
        public readonly string $overdueLevel,
        public readonly ?string $paidAt,
        public readonly ?string $paymentMethod,
        public readonly ?string $receiptNumber
    ) {}

    public static function fromEntity(Installment $installment): self
    {
        return new self(
            id: (string) $installment->getId(),
            number: $installment->getNumber(),
            total: $installment->getInstallmentNumber()->getTotal(),
            formattedNumber: $installment->getFormattedNumber(),
            amount: $installment->getAmount()->getValue(),
            dueDate: $installment->getDueDate()->format(),
            status: $installment->getStatus(),
            isPaid: $installment->isPaid(),
            isOverdue: $installment->isOverdue(),
            daysOverdue: $installment->getDaysOverdue(),
            overdueLevel: $installment->getOverdueLevel(),
            paidAt: $installment->getPaidAt()?->format('Y-m-d H:i:s'),
            paymentMethod: $installment->getPaymentMethod(),
            receiptNumber: $installment->getReceiptNumber()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'total' => $this->total,
            'formatted_number' => $this->formattedNumber,
            'amount' => $this->amount,
            'due_date' => $this->dueDate,
            'status' => $this->status,
            'is_paid' => $this->isPaid,
            'is_overdue' => $this->isOverdue,
            'days_overdue' => $this->daysOverdue,
            'overdue_level' => $this->overdueLevel,
            'paid_at' => $this->paidAt,
            'payment_method' => $this->paymentMethod,
            'receipt_number' => $this->receiptNumber,
        ];
    }
}
