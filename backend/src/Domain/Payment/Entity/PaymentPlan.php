<?php

declare(strict_types=1);

namespace App\Domain\Payment\Entity;

use App\Domain\Payment\ValueObject\Amount;
use App\Domain\Payment\ValueObject\DueDate;
use App\Domain\Payment\ValueObject\InstallmentNumber;
use App\Entity\Enrollment;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

/**
 * Represents a payment plan with installments.
 * Rich domain model with business logic.
 */
#[ORM\Entity]
#[ORM\Table(name: 'payment_plans')]
class PaymentPlan
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: Enrollment::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Enrollment $enrollment;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $totalAmount;

    #[ORM\Column(type: 'integer')]
    private int $numberOfInstallments;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $installmentAmount;

    #[ORM\Column(type: 'integer')]
    private int $dayOfMonth;

    #[ORM\Column(type: 'string', length: 20)]
    private string $status = 'active';

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $completedAt = null;

    #[ORM\OneToMany(mappedBy: 'paymentPlan', targetEntity: Installment::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['number' => 'ASC'])]
    private Collection $installments;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $metadata = null;

    private function __construct()
    {
        $this->id = Uuid::v4();
        $this->installments = new ArrayCollection();
        $this->createdAt = new DateTimeImmutable();
    }

    public static function create(
        Enrollment $enrollment,
        Amount $totalAmount,
        int $numberOfInstallments,
        int $dayOfMonth = 5
    ): self {
        if ($numberOfInstallments < 1 || $numberOfInstallments > 12) {
            throw new \InvalidArgumentException('Number of installments must be between 1 and 12');
        }

        if ($dayOfMonth < 1 || $dayOfMonth > 28) {
            throw new \InvalidArgumentException('Day of month must be between 1 and 28');
        }

        $plan = new self();
        $plan->enrollment = $enrollment;
        $plan->totalAmount = (string) $totalAmount->getValue();
        $plan->numberOfInstallments = $numberOfInstallments;
        $plan->dayOfMonth = $dayOfMonth;

        // Calculate installment amount
        $installmentValue = $totalAmount->getValue() / $numberOfInstallments;
        $plan->installmentAmount = (string) round($installmentValue, 2);

        // Generate installments
        $plan->generateInstallments();

        return $plan;
    }

    private function generateInstallments(): void
    {
        $installmentAmount = Amount::fromString($this->installmentAmount);
        $startDate = new DateTimeImmutable('first day of next month');

        for ($i = 1; $i <= $this->numberOfInstallments; $i++) {
            $dueDate = $startDate->modify(sprintf('+%d months', $i - 1));
            $dueDate = $dueDate->setDate(
                (int) $dueDate->format('Y'),
                (int) $dueDate->format('m'),
                min($this->dayOfMonth, (int) $dueDate->format('t'))
            );

            $installment = Installment::create(
                $this,
                new InstallmentNumber($i, $this->numberOfInstallments),
                $installmentAmount,
                DueDate::fromDateTime($dueDate)
            );

            $this->installments->add($installment);
        }
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getEnrollment(): Enrollment
    {
        return $this->enrollment;
    }

    public function getTotalAmount(): Amount
    {
        return Amount::fromString($this->totalAmount);
    }

    public function getNumberOfInstallments(): int
    {
        return $this->numberOfInstallments;
    }

    public function getInstallmentAmount(): Amount
    {
        return Amount::fromString($this->installmentAmount);
    }

    public function getDayOfMonth(): int
    {
        return $this->dayOfMonth;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getCompletedAt(): ?DateTimeImmutable
    {
        return $this->completedAt;
    }

    /**
     * @return Collection<int, Installment>
     */
    public function getInstallments(): Collection
    {
        return $this->installments;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    // Domain methods

    public function getPaidInstallments(): Collection
    {
        return $this->installments->filter(fn(Installment $i) => $i->isPaid());
    }

    public function getPendingInstallments(): Collection
    {
        return $this->installments->filter(fn(Installment $i) => !$i->isPaid());
    }

    public function getOverdueInstallments(): Collection
    {
        return $this->installments->filter(fn(Installment $i) => $i->isOverdue());
    }

    public function getNextPendingInstallment(): ?Installment
    {
        foreach ($this->installments as $installment) {
            if (!$installment->isPaid()) {
                return $installment;
            }
        }
        return null;
    }

    public function getTotalPaid(): Amount
    {
        $total = Amount::zero();
        foreach ($this->getPaidInstallments() as $installment) {
            $total = $total->add($installment->getAmount());
        }
        return $total;
    }

    public function getTotalPending(): Amount
    {
        return $this->getTotalAmount()->subtract($this->getTotalPaid());
    }

    public function getProgress(): float
    {
        $paid = $this->getPaidInstallments()->count();
        return ($paid / $this->numberOfInstallments) * 100;
    }

    public function isComplete(): bool
    {
        return $this->getPendingInstallments()->isEmpty();
    }

    public function hasOverdueInstallments(): bool
    {
        return !$this->getOverdueInstallments()->isEmpty();
    }

    public function markAsComplete(): void
    {
        if (!$this->isComplete()) {
            throw new \DomainException('Cannot mark incomplete plan as complete');
        }

        $this->status = 'completed';
        $this->completedAt = new DateTimeImmutable();
    }

    public function cancel(): void
    {
        $this->status = 'cancelled';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
