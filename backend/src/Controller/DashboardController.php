<?php

namespace App\Controller;

use App\Repository\EnrollmentRepository;
use App\Repository\PaymentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/dashboard')]
class DashboardController extends AbstractController
{
    public function __construct(
        private EnrollmentRepository $enrollmentRepository,
        private PaymentRepository $paymentRepository
    ) {
    }

    #[Route('/stats', name: 'api_dashboard_stats', methods: ['GET'])]
    public function stats(): JsonResponse
    {
        $currentYear = (int) date('Y');
        
        // Get enrollment stats
        $enrollmentStats = $this->enrollmentRepository->getStatsByGrade($currentYear);
        
        // Get active enrollments count
        $activeEnrollments = count($this->enrollmentRepository->findActiveByYear($currentYear));
        
        // Get payment stats
        $pendingPayments = count($this->paymentRepository->findPending());
        $overduePayments = count($this->paymentRepository->findOverdue());
        
        // Get daily collection
        $today = new \DateTime();
        $dailyTotal = $this->paymentRepository->getDailyTotal($today);

        return $this->json([
            'enrollments' => [
                'total' => $activeEnrollments,
                'byGrade' => $enrollmentStats,
            ],
            'payments' => [
                'pending' => $pendingPayments,
                'overdue' => $overduePayments,
                'dailyTotal' => $dailyTotal,
            ],
            'academicYear' => $currentYear,
        ]);
    }

    #[Route('/debtors', name: 'api_dashboard_debtors', methods: ['GET'])]
    public function debtors(): JsonResponse
    {
        $debtors = $this->paymentRepository->getDebtorsReport();
        
        return $this->json($debtors);
    }

    #[Route('/recent-enrollments', name: 'api_dashboard_recent_enrollments', methods: ['GET'])]
    public function recentEnrollments(Request $request): JsonResponse
    {
        $limit = $request->query->getInt('limit', 10);
        $currentYear = (int) date('Y');
        
        $enrollments = $this->enrollmentRepository->findActiveByYear($currentYear);
        
        // Sort by created date and limit
        usort($enrollments, fn($a, $b) => $b->getCreatedAt() <=> $a->getCreatedAt());
        $recentEnrollments = array_slice($enrollments, 0, $limit);
        
        return $this->json($recentEnrollments, Response::HTTP_OK, [], [
            'groups' => ['enrollment:read']
        ]);
    }
}
