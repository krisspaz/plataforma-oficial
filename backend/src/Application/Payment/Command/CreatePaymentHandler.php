<?php

declare(strict_types=1);

namespace App\Application\Payment\Command;

use App\Entity\Payment;
use App\Repository\EnrollmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreatePaymentHandler
{
    public function __construct(
        private readonly EnrollmentRepository $enrollmentRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger
    ) {}

    public function __invoke(CreatePaymentCommand $command): ?Payment
    {
        $enrollment = $this->enrollmentRepository->find($command->enrollmentId);

        if (!$enrollment) {
            return null;
        }

        $payment = new Payment();
        $payment->setEnrollment($enrollment);
        $payment->setAmount($command->amount);
        $payment->setPaymentType($command->paymentType);
        $payment->setPaymentMethod($command->paymentMethod);
        $payment->setStatus('pending');

        if ($command->dueDate) {
            try {
                $payment->setDueDate(new \DateTime($command->dueDate));
            } catch (\Exception $e) {
                throw new \InvalidArgumentException('Invalid date format');
            }
        }

        if ($command->metadata) {
            $payment->setMetadata($command->metadata);
        }

        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        $this->logger->info('Payment created', [
            'payment_id' => $payment->getId(),
            'enrollment_id' => $enrollment->getId(),
            'amount' => $payment->getAmount(),
        ]);

        return $payment;
    }
}
