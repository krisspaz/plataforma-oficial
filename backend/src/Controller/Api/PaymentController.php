<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Application\Payment\Command\CreatePaymentPlanCommand;
use App\Application\Payment\Command\CreatePaymentPlanHandler;
use App\Application\Payment\Command\RecordInstallmentPaymentCommand;
use App\Application\Payment\Command\RecordInstallmentPaymentHandler;
use App\Application\Payment\DTO\PaymentPlanDTO;
use App\Application\Payment\Query\GetDailyClosureHandler;
use App\Application\Payment\Query\GetDailyClosureQuery;
use App\Application\Payment\Query\GetDebtorsHandler;
use App\Application\Payment\Query\GetDebtorsQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/payments')]
class PaymentController extends AbstractController
{
    public function __construct(
        private readonly CreatePaymentPlanHandler $createPaymentPlanHandler,
        private readonly RecordInstallmentPaymentHandler $recordPaymentHandler,
        private readonly GetDebtorsHandler $getDebtorsHandler,
        private readonly GetDailyClosureHandler $getDailyClosureHandler
    ) {}

    /**
     * Create a new payment plan.
     */
    #[Route('/plans', name: 'api_payments_create_plan', methods: ['POST'])]
    #[IsGranted('ROLE_SECRETARIA')]
    public function createPlan(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $command = new CreatePaymentPlanCommand(
                enrollmentId: $data['enrollment_id'],
                totalAmount: (float) $data['total_amount'],
                numberOfInstallments: (int) $data['installments'],
                dayOfMonth: (int) ($data['day_of_month'] ?? 5),
                currency: $data['currency'] ?? 'GTQ',
                metadata: $data['metadata'] ?? null
            );

            $paymentPlan = ($this->createPaymentPlanHandler)($command);
            $dto = PaymentPlanDTO::fromEntity($paymentPlan);

            return $this->json([
                'success' => true,
                'message' => 'Plan de pagos creado exitosamente',
                'data' => $dto->toArray()
            ], Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\DomainException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_CONFLICT);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'error' => 'Error interno del servidor'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Record a payment for an installment.
     */
    #[Route('/installments/{id}/pay', name: 'api_payments_record', methods: ['POST'])]
    #[IsGranted('ROLE_SECRETARIA')]
    public function recordPayment(string $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $command = new RecordInstallmentPaymentCommand(
                installmentId: $id,
                paymentMethod: $data['payment_method'],
                receiptNumber: $data['receipt_number'] ?? null,
                recordedByUserId: $this->getUser()?->getId(),
                metadata: $data['metadata'] ?? null
            );

            $installment = ($this->recordPaymentHandler)($command);

            return $this->json([
                'success' => true,
                'message' => 'Pago registrado exitosamente',
                'data' => [
                    'installment_id' => (string) $installment->getId(),
                    'receipt_number' => $installment->getReceiptNumber(),
                    'paid_at' => $installment->getPaidAt()?->format('Y-m-d H:i:s'),
                ]
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\DomainException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_CONFLICT);
        }
    }

    /**
     * Get debtor report.
     */
    #[Route('/debtors', name: 'api_payments_debtors', methods: ['GET'])]
    #[IsGranted('ROLE_SECRETARIA')]
    public function getDebtors(Request $request): JsonResponse
    {
        try {
            $query = new GetDebtorsQuery(
                gradeId: $request->query->get('grade_id') ? (int) $request->query->get('grade_id') : null,
                level: $request->query->get('level'),
                minDaysOverdue: $request->query->get('min_days') ? (int) $request->query->get('min_days') : null
            );

            $report = ($this->getDebtorsHandler)($query);

            return $this->json([
                'success' => true,
                'data' => $report
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'error' => 'Error generando reporte de deudores'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get daily closure report.
     */
    #[Route('/daily-closure', name: 'api_payments_daily_closure', methods: ['GET'])]
    #[IsGranted('ROLE_SECRETARIA')]
    public function getDailyClosure(Request $request): JsonResponse
    {
        try {
            $query = new GetDailyClosureQuery(
                date: $request->query->get('date')
            );

            $report = ($this->getDailyClosureHandler)($query);

            return $this->json([
                'success' => true,
                'data' => $report
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'error' => 'Error generando corte del día'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get payment history for a student.
     */
    #[Route('/history/{studentId}', name: 'api_payments_history', methods: ['GET'])]
    #[IsGranted('ROLE_SECRETARIA')]
    public function getPaymentHistory(int $studentId): JsonResponse
    {
        // This would use a query handler
        return $this->json([
            'success' => true,
            'data' => [
                'student_id' => $studentId,
                'payments' => []
            ]
        ]);
    }
}
