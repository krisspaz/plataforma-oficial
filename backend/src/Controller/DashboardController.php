<?php

namespace App\Controller;

use App\Repository\EnrollmentRepository;
use App\Repository\PaymentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/dashboard')]
class DashboardController extends AbstractController
{
    public function __construct(
        private EnrollmentRepository $enrollmentRepository,
        private PaymentRepository $paymentRepository
    ) {}

    // ===== Helpers =====
    private function respond(mixed $data, int $status = 200): JsonResponse
    {
        return $this->json($data, $status);
    }

    // ===== Endpoints =====
    #[Route('/stats', name: 'api_dashboard_stats', methods: ['GET'])]
    public function stats(): JsonResponse
    {
        $currentYear = (int) date('Y');

        $enrollmentStats = $this->enrollmentRepository->getStatsByGrade($currentYear);
        $activeEnrollmentsCount = $this->enrollmentRepository->countActiveByYear($currentYear);

        $pendingPaymentsCount = $this->paymentRepository->countPending();
        $overduePaymentsCount = $this->paymentRepository->countOverdue();
        $dailyTotal = $this->paymentRepository->getDailyTotal(new \DateTime());

        return $this->respond([
            'enrollments' => [
                'total' => $activeEnrollmentsCount,
                'byGrade' => $enrollmentStats,
            ],
            'payments' => [
                'pending' => $pendingPaymentsCount,
                'overdue' => $overduePaymentsCount,
                'dailyTotal' => $dailyTotal,
            ],
            'academicYear' => $currentYear,
        ]);
    }

    #[Route('/debtors', name: 'api_dashboard_debtors', methods: ['GET'])]
    public function debtors(): JsonResponse
    {
        $debtors = $this->paymentRepository->getDebtorsReport();
        return $this->respond($debtors);
    }

    #[Route('/recent-enrollments', name: 'api_dashboard_recent_enrollments', methods: ['GET'])]
    public function recentEnrollments(int $limit = 10): JsonResponse
    {
        $currentYear = (int) date('Y');

        // Obtener directamente los últimos registros en la consulta
        $recentEnrollments = $this->enrollmentRepository->findRecentActiveByYear($currentYear, $limit);

        return $this->respond($recentEnrollments, 200, [], [
            'groups' => ['enrollment:read']
        ]);
    }
}
