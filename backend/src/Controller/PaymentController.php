<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Traits\ApiResponseTrait;
use App\Entity\Payment;
use App\Entity\User;
use App\Repository\PaymentRepository;
use App\Repository\EnrollmentRepository;
use App\Service\Payment\StripePaymentService;
use App\Service\Payment\PayPalPaymentService;
use App\Service\Payment\BACPaymentService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use OpenApi\Attributes as OA;

#[Route('/api/payments')]
#[OA\Tag(name: 'Payments')]
class PaymentController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private readonly PaymentRepository $paymentRepository,
        private readonly EnrollmentRepository $enrollmentRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly StripePaymentService $stripeService,
        private readonly PayPalPaymentService $paypalService,
        private readonly BACPaymentService $bacService,
        private readonly ValidatorInterface $validator,
        private readonly LoggerInterface $logger,
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
        $status = $request->query->get('status');
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 20)));

        if ($status) {
            $payments = $this->paymentRepository->findBy(
                ['status' => $status],
                ['createdAt' => 'DESC'],
                $limit,
                ($page - 1) * $limit
            );
            $total = $this->paymentRepository->count(['status' => $status]);
        } else {
            $payments = $this->paymentRepository->findBy(
                [],
                ['createdAt' => 'DESC'],
                $limit,
                ($page - 1) * $limit
            );
            $total = $this->paymentRepository->count([]);
        }

        return $this->paginated($payments, $total, $page, $limit, ['payment:read']);
    }

    #[Route('/pending', name: 'api_payments_pending', methods: ['GET'])]
    #[OA\Get(path: '/api/payments/pending', summary: 'Get pending payments')]
    public function pending(): JsonResponse
    {
        $payments = $this->paymentRepository->findPending();
        
        return $this->success($payments, 200, [], ['payment:read']);
    }

    #[Route('/overdue', name: 'api_payments_overdue', methods: ['GET'])]
    #[OA\Get(path: '/api/payments/overdue', summary: 'Get overdue payments')]
    public function overdue(): JsonResponse
    {
        $payments = $this->paymentRepository->findOverdue();
        
        return $this->success($payments, 200, [], ['payment:read']);
    }

    #[Route('/debtors', name: 'api_payments_debtors', methods: ['GET'])]
    #[OA\Get(path: '/api/payments/debtors', summary: 'Get debtors report')]
    public function debtors(): JsonResponse
    {
        $debtors = $this->paymentRepository->getDebtorsReport();
        
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

        $enrollment = $this->enrollmentRepository->find($data['enrollmentId']);
        if (!$enrollment) {
            return $this->notFound('Enrollment');
        }

        $payment = new Payment();
        $payment->setEnrollment($enrollment);
        $payment->setAmount((float) $data['amount']);
        $payment->setPaymentType($data['paymentType'] ?? 'contado');
        $payment->setPaymentMethod($data['paymentMethod'] ?? null);
        $payment->setStatus('pending');
        
        if (isset($data['dueDate'])) {
            try {
                $payment->setDueDate(new \DateTime($data['dueDate']));
            } catch (\Exception $e) {
                return $this->validationError(['dueDate' => 'Invalid date format']);
            }
        }

        if (isset($data['metadata'])) {
            $payment->setMetadata($data['metadata']);
        }

        $errors = $this->validator->validate($payment);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->validationError($errorMessages);
        }

        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        $this->logger->info('Payment created', [
            'payment_id' => $payment->getId(),
            'enrollment_id' => $enrollment->getId(),
            'amount' => $payment->getAmount(),
        ]);

        return $this->created($payment, ['payment:read']);
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

        $payment = $this->paymentRepository->find($id);
        
        if (!$payment) {
            return $this->notFound('Payment');
        }

        $data = json_decode($request->getContent(), true);
        $gateway = $data['gateway'] ?? 'stripe';

        try {
            $result = match ($gateway) {
                'stripe' => $this->stripeService->createPayment(
                    $payment->getAmount(),
                    $data['currency'] ?? 'usd',
                    ['payment_id' => $payment->getId()]
                ),
                'paypal' => $this->paypalService->createPayment(
                    $payment->getAmount(),
                    $data['currency'] ?? 'USD',
                    [
                        'payment_id' => $payment->getId(),
                        'return_url' => $data['return_url'] ?? '',
                        'cancel_url' => $data['cancel_url'] ?? '',
                    ]
                ),
                'bac' => $this->bacService->createPayment(
                    $payment->getAmount(),
                    $data['currency'] ?? 'GTQ',
                    [
                        'reference' => 'BAC-' . $payment->getId(),
                        'return_url' => $data['return_url'] ?? '',
                        'cancel_url' => $data['cancel_url'] ?? '',
                    ]
                ),
                default => throw new \InvalidArgumentException('Invalid payment gateway'),
            };

            $payment->setMetadata(array_merge($payment->getMetadata() ?? [], [
                'gateway' => $gateway,
                'gateway_payment_id' => $result['id'],
                'processed_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            ]));

            $this->entityManager->flush();

            $this->logger->info('Payment processed', [
                'payment_id' => $payment->getId(),
                'gateway' => $gateway,
                'gateway_payment_id' => $result['id'],
            ]);

            return $this->success([
                'payment' => $payment,
                'gateway_response' => $result,
            ], 200, [], ['payment:read']);

        } catch (\Exception $e) {
            $this->logger->error('Payment processing failed', [
                'payment_id' => $payment->getId(),
                'gateway' => $gateway,
                'error' => $e->getMessage(),
            ]);

            return $this->error('Payment processing failed: ' . $e->getMessage(), 500);
        }
    }

    #[Route('/{id}/mark-paid', name: 'api_payments_mark_paid', methods: ['POST'])]
    #[OA\Post(path: '/api/payments/{id}/mark-paid', summary: 'Manually mark payment as paid')]
    public function markAsPaid(int $id, Request $request): JsonResponse
    {
        $payment = $this->paymentRepository->find($id);
        
        if (!$payment) {
            return $this->notFound('Payment');
        }

        $data = json_decode($request->getContent(), true);

        $payment->markAsPaid();
        
        if (isset($data['paymentMethod'])) {
            $payment->setPaymentMethod($data['paymentMethod']);
        }

        if (isset($data['receipt'])) {
            $payment->setReceipt($data['receipt']);
        }

        if (isset($data['metadata'])) {
            $payment->setMetadata(array_merge(
                $payment->getMetadata() ?? [],
                $data['metadata']
            ));
        }

        $this->entityManager->flush();

        $this->logger->info('Payment marked as paid', [
            'payment_id' => $payment->getId(),
            'amount' => $payment->getAmount(),
        ]);

        return $this->success([
            'message' => 'Payment marked as paid successfully',
            'payment' => $payment,
        ], 200, [], ['payment:read']);
    }

    #[Route('/daily-total', name: 'api_payments_daily_total', methods: ['GET'])]
    #[OA\Get(path: '/api/payments/daily-total', summary: 'Get daily payment total')]
    public function dailyTotal(Request $request): JsonResponse
    {
        $date = $request->query->get('date');
        
        try {
            $dateObj = $date ? new \DateTime($date) : new \DateTime();
        } catch (\Exception $e) {
            return $this->validationError(['date' => 'Invalid date format']);
        }
        
        $total = $this->paymentRepository->getDailyTotal($dateObj);
        
        return $this->success([
            'date' => $dateObj->format('Y-m-d'),
            'total' => $total,
        ]);
    }

    #[Route('/enrollment/{enrollmentId}', name: 'api_payments_by_enrollment', methods: ['GET'])]
    #[OA\Get(path: '/api/payments/enrollment/{enrollmentId}', summary: 'Get payments by enrollment')]
    public function byEnrollment(int $enrollmentId): JsonResponse
    {
        $enrollment = $this->enrollmentRepository->find($enrollmentId);
        
        if (!$enrollment) {
            return $this->notFound('Enrollment');
        }

        $payments = $enrollment->getPayments();
        
        return $this->success([
            'payments' => $payments,
            'totalPaid' => $enrollment->getTotalPaid(),
            'totalPending' => $enrollment->getTotalPending(),
        ], 200, [], ['payment:read']);
    }

    #[Route('/{id}/refund', name: 'api_payments_refund', methods: ['POST'])]
    #[OA\Post(path: '/api/payments/{id}/refund', summary: 'Refund a payment')]
    public function refund(int $id, Request $request): JsonResponse
    {
        $payment = $this->paymentRepository->find($id);
        
        if (!$payment) {
            return $this->notFound('Payment');
        }

        if ($payment->getStatus() !== 'paid') {
            return $this->error('Only paid payments can be refunded', 400);
        }

        $data = json_decode($request->getContent(), true);
        $amount = $data['amount'] ?? $payment->getAmount();
        $metadata = $payment->getMetadata() ?? [];
        $gateway = $metadata['gateway'] ?? null;
        $gatewayPaymentId = $metadata['gateway_payment_id'] ?? null;

        if (!$gateway || !$gatewayPaymentId) {
            return $this->error('Payment was not processed through a gateway', 400);
        }

        try {
            $success = match ($gateway) {
                'stripe' => $this->stripeService->refund($gatewayPaymentId, $amount),
                'paypal' => $this->paypalService->refund($gatewayPaymentId, $amount),
                'bac' => $this->bacService->refund($gatewayPaymentId, $amount),
                default => throw new \InvalidArgumentException('Invalid payment gateway'),
            };

            if ($success) {
                $payment->setStatus('refunded');
                $payment->setMetadata(array_merge($metadata, [
                    'refunded_at' => (new \DateTime())->format('Y-m-d H:i:s'),
                    'refund_amount' => $amount,
                ]));

                $this->entityManager->flush();

                $this->logger->info('Payment refunded', [
                    'payment_id' => $payment->getId(),
                    'amount' => $amount,
                    'gateway' => $gateway,
                ]);

                return $this->success([
                    'message' => 'Payment refunded successfully',
                    'payment' => $payment,
                ], 200, [], ['payment:read']);
            }

            return $this->error('Refund failed', 500);

        } catch (\Exception $e) {
            $this->logger->error('Refund failed', [
                'payment_id' => $payment->getId(),
                'error' => $e->getMessage(),
            ]);

            return $this->error('Refund failed: ' . $e->getMessage(), 500);
        }
    }
}
