<?php

namespace App\Controller;

use App\Repository\StudentRepository;
use App\Repository\AIRiskScoreRepository;
use App\Service\AIRiskPredictionService;
use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/ai')]
class AIController extends AbstractController
{
    public function __construct(
        private StudentRepository $studentRepository,
        private AIRiskScoreRepository $riskScoreRepository,
        private AIRiskPredictionService $riskPredictionService,
        private NotificationService $notificationService
    ) {}

    // ===== Helper =====
    private function respond(mixed $data, int $status = 200, array $groups = []): JsonResponse
    {
        return $this->json($data, $status, [], $groups ? ['groups' => $groups] : []);
    }

    private function respondNotFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->respond(['error' => $message], 404);
    }

    private function respondBadRequest(string $message = 'Invalid request'): JsonResponse
    {
        return $this->respond(['error' => $message], 400);
    }

    private function respondInternalError(string $message = 'Internal error'): JsonResponse
    {
        return $this->respond(['error' => $message], 500);
    }

    // ===== SINGLE STUDENT RISK =====
    #[Route('/risk/student/{studentId}', name: 'api_ai_risk_student', methods: ['GET', 'POST'])]
    public function calculateStudentRisk(int $studentId): JsonResponse
    {
        $student = $this->studentRepository->find($studentId);
        if (!$student) return $this->respondNotFound('Student not found');

        try {
            $riskScore = $this->riskPredictionService->calculateRisk($student);

            if ($riskScore->isHighRisk()) {
                foreach ($student->getParents() as $parent) {
                    $this->notificationService->notifyAcademicRisk(
                        $parent->getUser(),
                        $student->getUser()->getFullName(),
                        $riskScore->getRiskLevel()
                    );
                }
            }

            return $this->respond([
                'student_id' => $studentId,
                'risk_level' => $riskScore->getRiskLevel(),
                'risk_percentage' => $riskScore->getRiskPercentage(),
                'factors' => $riskScore->getFactors(),
                'predictions' => $riskScore->getPredictions(),
                'calculated_at' => $riskScore->getCalculatedAt()->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->respondInternalError($e->getMessage());
        }
    }

    // ===== HIGH RISK STUDENTS =====
    #[Route('/risk/high-risk', name: 'api_ai_high_risk_students', methods: ['GET'])]
    public function highRiskStudents(): JsonResponse
    {
        $students = $this->riskPredictionService->getHighRiskStudents();
        return $this->respond([
            'students' => $students,
            'count' => count($students)
        ], 200, ['airiskscore:read']);
    }

    // ===== BATCH CALCULATION =====
    #[Route('/risk/batch-calculate', name: 'api_ai_batch_calculate', methods: ['POST'])]
    public function batchCalculate(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['studentIds']) || !is_array($data['studentIds'])) {
            return $this->respondBadRequest('Student IDs array required');
        }

        $students = array_map(fn($id) => $this->studentRepository->find($id), $data['studentIds']);
        $students = array_filter($students); // Remove nulls

        if (!$students) return $this->respondBadRequest('No valid students found');

        try {
            $results = $this->riskPredictionService->batchCalculateRisk($students);
            return $this->respond([
                'message' => 'Batch calculation completed',
                'results' => $results,
                'total' => count($results),
                'successful' => count(array_filter($results, fn($r) => $r['success']))
            ]);
        } catch (\Exception $e) {
            return $this->respondInternalError($e->getMessage());
        }
    }

    // ===== LATEST RISK SCORE =====
    #[Route('/risk/latest/{studentId}', name: 'api_ai_latest_risk', methods: ['GET'])]
    public function latestRisk(int $studentId): JsonResponse
    {
        $riskScore = $this->riskScoreRepository->findLatestForStudent($studentId);
        if (!$riskScore) return $this->respondNotFound('No risk score found for this student');

        return $this->respond($riskScore, 200, ['airiskscore:read']);
    }
}
