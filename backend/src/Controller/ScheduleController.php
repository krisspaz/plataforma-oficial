<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Traits\ApiResponseTrait;
use App\Repository\ScheduleRepository;
use App\Repository\SectionRepository;
use App\Service\ScheduleGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/api/schedules')]
#[OA\Tag(name: 'Schedules')]
class ScheduleController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private readonly ScheduleRepository $scheduleRepository,
        private readonly SectionRepository $sectionRepository,
        private readonly ScheduleGeneratorService $scheduleGenerator
    ) {}

    #[Route('/section/{sectionId}', name: 'api_schedules_by_section', methods: ['GET'])]
    #[OA\Get(path: '/api/schedules/section/{sectionId}', summary: 'Get schedules by section')]
    public function bySection(int $sectionId, Request $request): JsonResponse
    {
        $year = $request->query->getInt('year', (int) date('Y'));
        $schedules = $this->scheduleRepository->findBySection($sectionId, $year);
        
        return $this->success($schedules, 200, [], ['schedule:read']);
    }

    #[Route('/teacher/{teacherId}', name: 'api_schedules_by_teacher', methods: ['GET'])]
    #[OA\Get(path: '/api/schedules/teacher/{teacherId}', summary: 'Get schedules by teacher')]
    public function byTeacher(int $teacherId, Request $request): JsonResponse
    {
        $year = $request->query->getInt('year', (int) date('Y'));
        $schedules = $this->scheduleRepository->findByTeacher($teacherId, $year);
        
        return $this->success($schedules, 200, [], ['schedule:read']);
    }

    #[Route('/generate', name: 'api_schedules_generate', methods: ['POST'])]
    #[OA\Post(path: '/api/schedules/generate', summary: 'Generate schedule for a section')]
    public function generate(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['sectionId'])) {
            return $this->validationError(['sectionId' => 'Section ID required']);
        }

        $section = $this->sectionRepository->find($data['sectionId']);
        if (!$section) {
            return $this->notFound('Section');
        }

        $options = [
            'academicYear' => $data['academicYear'] ?? (int) date('Y'),
            'maxHoursPerDay' => $data['maxHoursPerDay'] ?? 6,
            'classroom' => $data['classroom'] ?? null
        ];

        try {
            $schedules = $this->scheduleGenerator->generateSchedule($section, $options);
            
            // Validate generated schedule
            $errors = $this->scheduleGenerator->validateSchedule($schedules);
            
            return $this->success([
                'message' => 'Schedule generated successfully',
                'schedules' => $schedules,
                'count' => count($schedules),
                'errors' => $errors
            ], 201, [], ['schedule:read']);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Failed to generate schedule',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
