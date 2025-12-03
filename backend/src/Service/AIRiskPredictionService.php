<?php

namespace App\Service;

use App\Entity\AIRiskScore;
use App\Entity\Student;
use App\Repository\AIRiskScoreRepository;
use Doctrine\ORM\EntityManagerInterface;

class AIRiskPredictionService
{
    public function __construct(
        private AIRiskScoreRepository $riskScoreRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Calculate academic risk for a student
     */
    public function calculateRisk(Student $student): AIRiskScore
    {
        $factors = $this->analyzeFactors($student);
        $riskLevel = $this->determineRiskLevel($factors);
        $predictions = $this->generatePredictions($factors);

        // Create or update risk score
        $riskScore = $this->riskScoreRepository->findLatestForStudent($student->getId());
        
        if (!$riskScore) {
            $riskScore = new AIRiskScore();
            $riskScore->setStudent($student);
        }

        $riskScore->setRiskLevel($riskLevel);
        $riskScore->setFactors($factors);
        $riskScore->setPredictions($predictions);
        $riskScore->setCalculatedAt(new \DateTime());

        $this->entityManager->persist($riskScore);
        $this->entityManager->flush();

        return $riskScore;
    }

    /**
     * Analyze risk factors
     */
    private function analyzeFactors(Student $student): array
    {
        $factors = [];

        // TODO: Implement actual data analysis
        // For now, using simulated data
        
        // Attendance factor (-1 to 1, negative is bad)
        $factors['attendance'] = $this->calculateAttendanceFactor($student);
        
        // Grades factor (-1 to 1, negative is bad)
        $factors['grades'] = $this->calculateGradesFactor($student);
        
        // Behavior factor (-1 to 1, negative is bad)
        $factors['behavior'] = $this->calculateBehaviorFactor($student);
        
        // Participation factor (-1 to 1, negative is bad)
        $factors['participation'] = $this->calculateParticipationFactor($student);
        
        // Payment status factor (-1 to 1, negative is bad)
        $factors['payment_status'] = $this->calculatePaymentFactor($student);

        return $factors;
    }

    /**
     * Determine risk level based on factors
     */
    private function determineRiskLevel(array $factors): string
    {
        $totalScore = array_sum($factors);
        $averageScore = $totalScore / count($factors);

        if ($averageScore >= 0.5) {
            return 'low';
        } elseif ($averageScore >= 0) {
            return 'medium';
        } elseif ($averageScore >= -0.5) {
            return 'high';
        } else {
            return 'critical';
        }
    }

    /**
     * Generate predictions
     */
    private function generatePredictions(array $factors): array
    {
        $totalScore = array_sum($factors);
        $averageScore = $totalScore / count($factors);
        
        // Convert to risk percentage (0-100)
        $riskPercentage = max(0, min(100, (1 - $averageScore) * 50));

        $predictions = [
            'risk_percentage' => round($riskPercentage, 2),
            'dropout_probability' => round($riskPercentage * 0.8, 2),
            'needs_intervention' => $riskPercentage > 50,
            'recommended_actions' => $this->getRecommendedActions($factors, $riskPercentage)
        ];

        return $predictions;
    }

    /**
     * Get recommended actions based on risk factors
     */
    private function getRecommendedActions(array $factors, float $riskPercentage): array
    {
        $actions = [];

        if ($factors['attendance'] < -0.3) {
            $actions[] = 'Mejorar asistencia - contactar a padres';
        }

        if ($factors['grades'] < -0.3) {
            $actions[] = 'Reforzamiento académico en materias débiles';
        }

        if ($factors['behavior'] < -0.3) {
            $actions[] = 'Intervención de orientación psicológica';
        }

        if ($factors['participation'] < -0.3) {
            $actions[] = 'Fomentar participación en clase';
        }

        if ($factors['payment_status'] < -0.3) {
            $actions[] = 'Revisar situación financiera con administración';
        }

        if ($riskPercentage > 70) {
            $actions[] = 'URGENTE: Reunión con padres y coordinación';
        }

        return $actions;
    }

    // Simulated factor calculations (replace with real data)
    
    private function calculateAttendanceFactor(Student $student): float
    {
        // TODO: Calculate from actual attendance records
        return rand(-100, 100) / 100;
    }

    private function calculateGradesFactor(Student $student): float
    {
        // TODO: Calculate from actual grade records
        return rand(-100, 100) / 100;
    }

    private function calculateBehaviorFactor(Student $student): float
    {
        // TODO: Calculate from behavior records
        return rand(-100, 100) / 100;
    }

    private function calculateParticipationFactor(Student $student): float
    {
        // TODO: Calculate from participation records
        return rand(-100, 100) / 100;
    }

    private function calculatePaymentFactor(Student $student): float
    {
        // TODO: Calculate from payment records
        $enrollments = $student->getEnrollments();
        if ($enrollments->isEmpty()) {
            return 0;
        }

        $latestEnrollment = $enrollments->last();
        $totalPending = $latestEnrollment->getTotalPending();
        
        if ($totalPending > 1000) {
            return -0.5;
        } elseif ($totalPending > 500) {
            return -0.2;
        } else {
            return 0.5;
        }
    }

    /**
     * Get high-risk students
     */
    public function getHighRiskStudents(): array
    {
        return $this->riskScoreRepository->findHighRiskStudents();
    }

    /**
     * Batch calculate risk for all students
     */
    public function batchCalculateRisk(array $students): array
    {
        $results = [];

        foreach ($students as $student) {
            try {
                $riskScore = $this->calculateRisk($student);
                $results[] = [
                    'student_id' => $student->getId(),
                    'risk_level' => $riskScore->getRiskLevel(),
                    'risk_percentage' => $riskScore->getRiskPercentage(),
                    'success' => true
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'student_id' => $student->getId(),
                    'error' => $e->getMessage(),
                    'success' => false
                ];
            }
        }

        return $results;
    }
}
