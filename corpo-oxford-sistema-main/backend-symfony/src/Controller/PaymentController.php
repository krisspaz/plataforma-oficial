<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Repository\PaymentRepository;
use App\Repository\EnrollmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/payments')]
class PaymentController extends AbstractController
{
    public function __construct(
        private PaymentRepository $paymentRepository,
        private EnrollmentRepository $enrollmentRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('', name: 'api_payments_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $status = $request->query->get('status');
        
        if ($status) {
            $payments = $this->paymentRepository->findBy(['status' => $status]);
        } else {
            $payments = $this->paymentRepository->findAll();
        }
        
        return $this->json($payments, Response::HTTP_OK, [], [
            'groups' => ['payment:read']
        ]);
    }

    #[Route('/pending', name: 'api_payments_pending', methods: ['GET'])]
    public function pending(): JsonResponse
    {
        $payments = $this->paymentRepository->findPending();
        
        return $this->json($payments, Response::HTTP_OK, [], [
            'groups' => ['payment:read']
        ]);
    }

    #[Route('/overdue', name: 'api_payments_overdue', methods: ['GET'])]
    public function overdue(): JsonResponse
    {
        $payments = $this->paymentRepository->findOverdue();
        
        return $this->json($payments, Response::HTTP_OK, [], [
            'groups' => ['payment:read']
        ]);
    }

    #[Route('/debtors', name: 'api_payments_debtors', methods: ['GET'])]
    public function debtors(): JsonResponse
    {
        $debtors = $this->paymentRepository->getDebtorsReport();
        
        return $this->json($debtors);
    }

    #[Route('', name: 'api_payments_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['enrollmentId']) || !isset($data['amount'])) {
            return $this->json([
                'error' => 'Enrollment ID and amount are required'
            ], Response::HTTP_BAD_REQUEST);
        }

        $enrollment = $this->enrollmentRepository->find($data['enrollmentId']);
        if (!$enrollment) {
            return $this->json(['error' => 'Enrollment not found'], Response::HTTP_NOT_FOUND);
        }

        $payment = new Payment();
        $payment->setEnrollment($enrollment);
        $payment->setAmount($data['amount']);
        $payment->setPaymentType($data['paymentType'] ?? 'contado');
        $payment->setPaymentMethod($data['paymentMethod'] ?? null);
        $payment->setStatus('pending');
        
        if (isset($data['dueDate'])) {
            $payment->setDueDate(new \DateTime($data['dueDate']));
        }

        if (isset($data['metadata'])) {
            $payment->setMetadata($data['metadata']);
        }

        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        return $this->json($payment, Response::HTTP_CREATED, [], [
            'groups' => ['payment:read']
        ]);
    }

    #[Route('/{id}/mark-paid', name: 'api_payments_mark_paid', methods: ['POST'])]
    public function markAsPaid(int $id, Request $request): JsonResponse
    {
        $payment = $this->paymentRepository->find($id);
        
        if (!$payment) {
            return $this->json(['error' => 'Payment not found'], Response::HTTP_NOT_FOUND);
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

        return $this->json([
            'message' => 'Payment marked as paid successfully',
            'payment' => $payment
        ], Response::HTTP_OK, [], [
            'groups' => ['payment:read']
        ]);
    }

    #[Route('/daily-total', name: 'api_payments_daily_total', methods: ['GET'])]
    public function dailyTotal(Request $request): JsonResponse
    {
        $date = $request->query->get('date');
        $dateObj = $date ? new \DateTime($date) : new \DateTime();
        
        $total = $this->paymentRepository->getDailyTotal($dateObj);
        
        return $this->json([
            'date' => $dateObj->format('Y-m-d'),
            'total' => $total
        ]);
    }

    #[Route('/enrollment/{enrollmentId}', name: 'api_payments_by_enrollment', methods: ['GET'])]
    public function byEnrollment(int $enrollmentId): JsonResponse
    {
        $enrollment = $this->enrollmentRepository->find($enrollmentId);
        
        if (!$enrollment) {
            return $this->json(['error' => 'Enrollment not found'], Response::HTTP_NOT_FOUND);
        }

        $payments = $enrollment->getPayments();
        
        return $this->json([
            'payments' => $payments,
            'totalPaid' => $enrollment->getTotalPaid(),
            'totalPending' => $enrollment->getTotalPending()
        ], Response::HTTP_OK, [], [
            'groups' => ['payment:read']
        ]);
    }
}
