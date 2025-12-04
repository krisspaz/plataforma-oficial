<?php

declare(strict_types=1);

namespace App\Application\Payment\Command;

use App\Domain\Payment\Entity\PaymentPlan;
use App\Domain\Payment\Repository\PaymentPlanRepositoryInterface;
use App\Domain\Payment\Service\PaymentPlanCalculator;
use App\Repository\EnrollmentRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreatePaymentPlanHandler
{
    public function __construct(
        private readonly EnrollmentRepository $enrollmentRepository,
        private readonly PaymentPlanRepositoryInterface $paymentPlanRepository,
        private readonly PaymentPlanCalculator $calculator
    ) {}

    public function __invoke(CreatePaymentPlanCommand $command): PaymentPlan
    {
        $enrollment = $this->enrollmentRepository->find($command->enrollmentId);

        if (!$enrollment) {
            throw new \InvalidArgumentException(
                sprintf('Enrollment with ID %d not found', $command->enrollmentId)
            );
        }

        // Check for existing active plan
        $existingPlan = $this->paymentPlanRepository->findActiveByEnrollment($enrollment);
        if ($existingPlan) {
            throw new \DomainException('An active payment plan already exists for this enrollment');
        }

        // Validate plan is viable
        $amount = $command->getTotalAmountAsValueObject();
        if (!$this->calculator->isViablePlan($enrollment, $amount, $command->numberOfInstallments)) {
            throw new \DomainException('The proposed payment plan is not viable');
        }

        // Create the payment plan
        $paymentPlan = PaymentPlan::create(
            $enrollment,
            $amount,
            $command->numberOfInstallments,
            $command->dayOfMonth
        );

        if ($command->metadata) {
            $paymentPlan->setMetadata($command->metadata);
        }

        $this->paymentPlanRepository->save($paymentPlan);

        return $paymentPlan;
    }
}
