<?php

namespace App\Controller;

use App\Repository\StudentRepository;
use App\Repository\AIRiskScoreRepository;
use App\Service\AIRiskPredictionService;
use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/ai')]
class AIController extends AbstractController
{
    public function __construct(
        private StudentRepository $studentRepository,
        private AIRiskScoreRepository $riskScoreRepository,
        private AIRiskPredictionService $riskPredictionService,
        private NotificationService $notificationService
    ) {
    }

    #[Route('/risk/student/{studentId}', name: 'api_ai_risk_student', methods: ['GET', 'POST'])]
    public function calculateStudentRisk(int $studentId): JsonResponse
    {
        $student = $this->studentRepository->find($studentId);
        
        if (!$student) {
            return $this->json(['error' => 'Student not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $riskScore = $this->riskPredictionService->calculateRisk($student);
            
            // Send notification if high risk
            if ($riskScore->isHighRisk()) {
                foreach ($student->getParents() as $parent) {
                    $this->notificationService->notifyAcademicRisk(
                        $parent->getUser(),
                        $student->getUser()->getFirstName() . ' ' . $student->getUser()->getLastName(),
                        $riskScore->getRiskLevel()
                    );
                }
            }

            return $this->json([
                'student_id' => $studentId,
                'risk_level' => $riskScore->getRiskLevel(),
                'risk_percentage' => $riskScore->getRiskPercentage(),
                'factors' => $riskScore->getFactors(),
                'predictions' => $riskScore->getPredictions(),
                'calculated_at' => $riskScore->getCalculatedAt()->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Failed to calculate risk',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/risk/high-risk', name: 'api_ai_high_risk_students', methods: ['GET'])]
    public function highRiskStudents(): JsonResponse
    {
        $highRiskStudents = $this->riskPredictionService->getHighRiskStudents();
        
        return $this->json([
            'students' => $highRiskStudents,
            'count' => count($highRiskStudents)
        ], Response::HTTP_OK, [], [
            'groups' => ['airiskscore:read']
        ]);
    }

    #[Route('/risk/batch-calculate', name: 'api_ai_batch_calculate', methods: ['POST'])]
    public function batchCalculate(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['studentIds']) || !is_array($data['studentIds'])) {
            return $this->json(['error' => 'Student IDs array required'], Response::HTTP_BAD_REQUEST);
        }

        $students = [];
        foreach ($data['studentIds'] as $studentId) {
            $student = $this->studentRepository->find($studentId);
            if ($student) {
                $students[] = $student;
            }
        }

        if (empty($students)) {
            return $this->json(['error' => 'No valid students found'], Response::HTTP_BAD_REQUEST);
        }

        $results = $this->riskPredictionService->batchCalculateRisk($students);

        return $this->json([
            'message' => 'Batch calculation completed',
            'results' => $results,
            'total' => count($results),
            'successful' => count(array_filter($results, fn($r) => $r['success']))
        ]);
    }

    #[Route('/risk/latest/{studentId}', name: 'api_ai_latest_risk', methods: ['GET'])]
    public function latestRisk(int $studentId): JsonResponse
    {
        $riskScore = $this->riskScoreRepository->findLatestForStudent($studentId);
        
        if (!$riskScore) {
            return $this->json(['error' => 'No risk score found for this student'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($riskScore, Response::HTTP_OK, [], [
            'groups' => ['airiskscore:read']
        ]);
    }
}
