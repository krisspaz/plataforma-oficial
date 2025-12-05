<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Payment\Command\CreatePaymentCommand;
use App\Application\Payment\Command\MarkPaymentAsPaidCommand;
use App\Application\Payment\Command\ProcessPaymentCommand;
use App\Application\Payment\Command\RefundPaymentCommand;
use App\Application\Payment\Query\GetDailyTotalQuery;
use App\Application\Payment\Query\GetDebtorsQuery;
use App\Application\Payment\Query\GetOverduePaymentsQuery;
use App\Application\Payment\Query\GetPaymentsByEnrollmentQuery;
use App\Application\Payment\Query\GetPaymentsQuery;
use App\Application\Payment\Query\GetPendingPaymentsQuery;
use App\Controller\Traits\ApiResponseTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use OpenApi\Attributes as OA;

#[Route('/api/payments')]
#[OA\Tag(name: 'Payments')]
class PaymentController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private readonly MessageBusInterface $queryBus,
        private readonly MessageBusInterface $commandBus,
        private readonly RateLimiterFactory $apiLimiter
    ) {}

    #[Route('', name: 'api_payments_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/payments',
        summary: 'Get payments with filters and pagination',
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: 'limit', in: 'query', schema: new OA\Schema(type: 'integer', default: 20)),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = new GetPaymentsQuery(
            status: $request->query->get('status'),
            page: (int) $request->query->get('page', 1),
            limit: (int) $request->query->get('limit', 20)
        );

        $result = $this->handleQuery($query);

        return $this->paginated(
            $result['payments'],
            $result['total'],
            $result['page'],
            $result['limit'],
            ['payment:read']
        );
    }

    #[Route('/pending', name: 'api_payments_pending', methods: ['GET'])]
    #[OA\Get(path: '/api/payments/pending', summary: 'Get pending payments')]
    public function pending(): JsonResponse
    {
        $payments = $this->handleQuery(new GetPendingPaymentsQuery());

        return $this->success($payments, 200, [], ['payment:read']);
    }

    #[Route('/overdue', name: 'api_payments_overdue', methods: ['GET'])]
    #[OA\Get(path: '/api/payments/overdue', summary: 'Get overdue payments')]
    public function overdue(): JsonResponse
    {
        $payments = $this->handleQuery(new GetOverduePaymentsQuery());

        return $this->success($payments, 200, [], ['payment:read']);
    }

    #[Route('/debtors', name: 'api_payments_debtors', methods: ['GET'])]
    #[OA\Get(path: '/api/payments/debtors', summary: 'Get debtors report')]
    public function debtors(): JsonResponse
    {
        $debtors = $this->handleQuery(new GetDebtorsQuery());

        return $this->success($debtors);
    }

    #[Route('', name: 'api_payments_create', methods: ['POST'])]
    #[OA\Post(path: '/api/payments', summary: 'Create a new payment')]
    public function create(Request $request): JsonResponse
    {
        // Rate Limiting
        $limiter = $this->apiLimiter->create($request->getClientIp());
        if (false === $limiter->consume(1)->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['enrollmentId'], $data['amount'])) {
            return $this->validationError([
                'enrollmentId' => 'Enrollment ID is required',
                'amount' => 'Amount is required',
            ]);
        }

        try {
            $command = new CreatePaymentCommand(
                enrollmentId: (int) $data['enrollmentId'],
                amount: (float) $data['amount'],
                paymentType: $data['paymentType'] ?? 'contado',
                paymentMethod: $data['paymentMethod'] ?? null,
                dueDate: $data['dueDate'] ?? null,
                metadata: $data['metadata'] ?? null
            );

            $payment = $this->handleCommand($command);

            if (!$payment) {
                return $this->notFound('Enrollment');
            }

            return $this->created($payment, ['payment:read']);
        } catch (\InvalidArgumentException $e) {
            return $this->validationError(['dueDate' => $e->getMessage()]);
        }
    }

    #[Route('/{id}/process', name: 'api_payments_process', methods: ['POST'])]
    #[OA\Post(path: '/api/payments/{id}/process', summary: 'Process payment with gateway')]
    public function process(int $id, Request $request): JsonResponse
    {
        // Rate Limiting
        $limiter = $this->apiLimiter->create($request->getClientIp());
        if (false === $limiter->consume(1)->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }

        $data = json_decode($request->getContent(), true);

        try {
            $command = new ProcessPaymentCommand(
                paymentId: $id,
                gateway: $data['gateway'] ?? 'stripe',
                currency: $data['currency'] ?? 'usd',
                returnUrl: $data['return_url'] ?? null,
                cancelUrl: $data['cancel_url'] ?? null
            );

            $result = $this->handleCommand($command);

            return $this->success($result, 200, [], ['payment:read']);
        } catch (\InvalidArgumentException $e) {
            return $this->notFound('Payment');
        } catch (\Exception $e) {
            return $this->error('Payment processing failed: ' . $e->getMessage(), 500);
        }
    }

    #[Route('/{id}/mark-paid', name: 'api_payments_mark_paid', methods: ['POST'])]
    #[OA\Post(path: '/api/payments/{id}/mark-paid', summary: 'Manually mark payment as paid')]
    public function markAsPaid(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $command = new MarkPaymentAsPaidCommand(
            paymentId: $id,
            paymentMethod: $data['paymentMethod'] ?? null,
            receipt: $data['receipt'] ?? null,
            metadata: $data['metadata'] ?? null
        );

        $payment = $this->handleCommand($command);

        if (!$payment) {
            return $this->notFound('Payment');
        }

        return $this->success([
            'message' => 'Payment marked as paid successfully',
            'payment' => $payment,
        ], 200, [], ['payment:read']);
    }

    #[Route('/daily-total', name: 'api_payments_daily_total', methods: ['GET'])]
    #[OA\Get(path: '/api/payments/daily-total', summary: 'Get daily payment total')]
    public function dailyTotal(Request $request): JsonResponse
    {
        $dateStr = $request->query->get('date');

        try {
            $date = $dateStr ? new \DateTime($dateStr) : null;
        } catch (\Exception $e) {
            return $this->validationError(['date' => 'Invalid date format']);
        }

        $result = $this->handleQuery(new GetDailyTotalQuery($date));

        return $this->success($result);
    }

    #[Route('/enrollment/{enrollmentId}', name: 'api_payments_by_enrollment', methods: ['GET'])]
    #[OA\Get(path: '/api/payments/enrollment/{enrollmentId}', summary: 'Get payments by enrollment')]
    public function byEnrollment(int $enrollmentId): JsonResponse
    {
        $result = $this->handleQuery(new GetPaymentsByEnrollmentQuery($enrollmentId));

        if (!$result) {
            return $this->notFound('Enrollment');
        }

        return $this->success([
            'payments' => $result['payments'],
            'totalPaid' => $result['totalPaid'],
            'totalPending' => $result['totalPending'],
        ], 200, [], ['payment:read']);
    }

    #[Route('/{id}/refund', name: 'api_payments_refund', methods: ['POST'])]
    #[OA\Post(path: '/api/payments/{id}/refund', summary: 'Refund a payment')]
    public function refund(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $command = new RefundPaymentCommand(
                paymentId: $id,
                amount: isset($data['amount']) ? (float) $data['amount'] : null
            );

            $result = $this->handleCommand($command);

            if ($result['success']) {
                return $this->success([
                    'message' => 'Payment refunded successfully',
                    'payment' => $result['payment'],
                ], 200, [], ['payment:read']);
            }

            return $this->error('Refund failed', 500);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->error('Refund failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Handle a query and return the result
     */
    private function handleQuery(object $query): mixed
    {
        $envelope = $this->queryBus->dispatch($query);
        $handledStamp = $envelope->last(HandledStamp::class);

        return $handledStamp?->getResult();
    }

    /**
     * Handle a command and return the result
     */
    private function handleCommand(object $command): mixed
    {
        $envelope = $this->commandBus->dispatch($command);
        $handledStamp = $envelope->last(HandledStamp::class);

        return $handledStamp?->getResult();
    }
}
