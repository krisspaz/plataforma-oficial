<?php

declare(strict_types=1);

namespace App\Domain\Payment\Entity;

use App\Domain\Payment\ValueObject\Amount;
use App\Domain\Payment\ValueObject\DueDate;
use App\Domain\Payment\ValueObject\InstallmentNumber;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Represents a single installment within a payment plan.
 */
#[ORM\Entity]
#[ORM\Table(name: 'installments')]
class Installment
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: PaymentPlan::class, inversedBy: 'installments')]
    #[ORM\JoinColumn(nullable: false)]
    private PaymentPlan $paymentPlan;

    #[ORM\Column(type: 'integer')]
    private int $number;

    #[ORM\Column(type: 'integer')]
    private int $totalInstallments;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $amount;

    #[ORM\Column(type: 'date_immutable')]
    private DateTimeImmutable $dueDate;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $paidAt = null;

    #[ORM\Column(type: 'string', length: 20)]
    private string $status = 'pending';

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $receiptNumber = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $paymentMethod = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $metadata = null;

    private function __construct()
    {
        $this->id = Uuid::v4();
    }

    public static function create(
        PaymentPlan $paymentPlan,
        InstallmentNumber $installmentNumber,
        Amount $amount,
        DueDate $dueDate
    ): self {
        $installment = new self();
        $installment->paymentPlan = $paymentPlan;
        $installment->number = $installmentNumber->getNumber();
        $installment->totalInstallments = $installmentNumber->getTotal();
        $installment->amount = (string) $amount->getValue();
        $installment->dueDate = $dueDate->getDate();

        return $installment;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getPaymentPlan(): PaymentPlan
    {
        return $this->paymentPlan;
    }

    public function getInstallmentNumber(): InstallmentNumber
    {
        return new InstallmentNumber($this->number, $this->totalInstallments);
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function getAmount(): Amount
    {
        return Amount::fromString($this->amount);
    }

    public function getDueDate(): DueDate
    {
        return DueDate::fromDateTime($this->dueDate);
    }

    public function getPaidAt(): ?DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getReceiptNumber(): ?string
    {
        return $this->receiptNumber;
    }

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    // Domain methods

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isOverdue(): bool
    {
        return !$this->isPaid() && $this->getDueDate()->isOverdue();
    }

    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return $this->getDueDate()->getDaysOverdue();
    }

    public function getOverdueLevel(): string
    {
        return $this->getDueDate()->getOverdueLevel();
    }

    public function markAsPaid(string $paymentMethod, ?string $receiptNumber = null): void
    {
        if ($this->isPaid()) {
            throw new \DomainException('Installment is already paid');
        }

        $this->status = 'paid';
        $this->paidAt = new DateTimeImmutable();
        $this->paymentMethod = $paymentMethod;
        $this->receiptNumber = $receiptNumber ?? $this->generateReceiptNumber();

        // Check if plan is complete
        if ($this->paymentPlan->isComplete()) {
            $this->paymentPlan->markAsComplete();
        }
    }

    public function cancel(): void
    {
        if ($this->isPaid()) {
            throw new \DomainException('Cannot cancel a paid installment');
        }

        $this->status = 'cancelled';
    }

    public function setMetadata(?array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    private function generateReceiptNumber(): string
    {
        return sprintf('REC-%s-%s', date('Ymd'), strtoupper(bin2hex(random_bytes(4))));
    }

    public function isFirst(): bool
    {
        return $this->number === 1;
    }

    public function isLast(): bool
    {
        return $this->number === $this->totalInstallments;
    }

    public function getFormattedNumber(): string
    {
        return sprintf('%d/%d', $this->number, $this->totalInstallments);
    }
}
