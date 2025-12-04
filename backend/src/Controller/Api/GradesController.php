<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Application\Grades\Command\CloseBimesterCommand;
use App\Application\Grades\Command\RecordGradeCommand;
use App\Application\Grades\Query\GetStudentGradesQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/grades')]
class GradesController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {}

    /**
     * Record a grade for a student.
     */
    #[Route('', name: 'api_grades_record', methods: ['POST'])]
    #[IsGranted('ROLE_TEACHER')]
    public function recordGrade(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $command = new RecordGradeCommand(
                studentId: $data['student_id'],
                subjectId: $data['subject_id'],
                teacherId: $data['teacher_id'],
                bimester: $data['bimester'],
                academicYear: $data['academic_year'] ?? (int) date('Y'),
                grade: (float) $data['grade'],
                comments: $data['comments'] ?? null
            );

            $this->messageBus->dispatch($command);

            return $this->json([
                'success' => true,
                'message' => 'Grade recorded successfully'
            ], Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'error' => 'Error recording grade'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get grades for a student.
     */
    #[Route('/student/{studentId}', name: 'api_grades_student', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getStudentGrades(int $studentId, Request $request): JsonResponse
    {
        try {
            $query = new GetStudentGradesQuery(
                studentId: $studentId,
                bimester: $request->query->getInt('bimester') ?: null,
                academicYear: $request->query->getInt('year') ?: null
            );

            $envelope = $this->messageBus->dispatch($query);
            $handledStamp = $envelope->last(HandledStamp::class);
            $grades = $handledStamp->getResult();

            return $this->json([
                'success' => true,
                'data' => $grades
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Close a bimester for grade entry.
     */
    #[Route('/bimester/close', name: 'api_grades_close_bimester', methods: ['POST'])]
    #[IsGranted('ROLE_COORDINATOR')]
    public function closeBimester(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $user = $this->getUser();

            $command = new CloseBimesterCommand(
                gradeId: $data['grade_id'],
                bimester: $data['bimester'],
                academicYear: $data['academic_year'] ?? (int) date('Y'),
                userId: $user->getId()
            );

            $this->messageBus->dispatch($command);

            return $this->json([
                'success' => true,
                'message' => 'Bimester closed successfully'
            ]);
        } catch (\DomainException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_CONFLICT);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'error' => 'Error closing bimester'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Bulk record grades for multiple students.
     */
    #[Route('/bulk', name: 'api_grades_bulk', methods: ['POST'])]
    #[IsGranted('ROLE_TEACHER')]
    public function bulkRecordGrades(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $grades = $data['grades'] ?? [];
            $teacherId = $data['teacher_id'];
            $subjectId = $data['subject_id'];
            $bimester = $data['bimester'];
            $academicYear = $data['academic_year'] ?? (int) date('Y');

            $recorded = 0;
            $errors = [];

            foreach ($grades as $entry) {
                try {
                    $command = new RecordGradeCommand(
                        studentId: $entry['student_id'],
                        subjectId: $subjectId,
                        teacherId: $teacherId,
                        bimester: $bimester,
                        academicYear: $academicYear,
                        grade: (float) $entry['grade'],
                        comments: $entry['comments'] ?? null
                    );

                    $this->messageBus->dispatch($command);
                    $recorded++;
                } catch (\Throwable $e) {
                    $errors[] = [
                        'student_id' => $entry['student_id'],
                        'error' => $e->getMessage()
                    ];
                }
            }

            return $this->json([
                'success' => true,
                'recorded' => $recorded,
                'errors' => $errors
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'error' => 'Error in bulk operation'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
