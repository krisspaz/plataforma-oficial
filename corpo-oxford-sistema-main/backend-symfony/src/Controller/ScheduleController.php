<?php

namespace App\Controller;

use App\Repository\ScheduleRepository;
use App\Repository\SectionRepository;
use App\Service\ScheduleGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/schedules')]
class ScheduleController extends AbstractController
{
    public function __construct(
        private ScheduleRepository $scheduleRepository,
        private SectionRepository $sectionRepository,
        private ScheduleGeneratorService $scheduleGenerator
    ) {
    }

    #[Route('/section/{sectionId}', name: 'api_schedules_by_section', methods: ['GET'])]
    public function bySection(int $sectionId, Request $request): JsonResponse
    {
        $year = $request->query->getInt('year', (int) date('Y'));
        $schedules = $this->scheduleRepository->findBySection($sectionId, $year);
        
        return $this->json($schedules, Response::HTTP_OK, [], [
            'groups' => ['schedule:read']
        ]);
    }

    #[Route('/teacher/{teacherId}', name: 'api_schedules_by_teacher', methods: ['GET'])]
    public function byTeacher(int $teacherId, Request $request): JsonResponse
    {
        $year = $request->query->getInt('year', (int) date('Y'));
        $schedules = $this->scheduleRepository->findByTeacher($teacherId, $year);
        
        return $this->json($schedules, Response::HTTP_OK, [], [
            'groups' => ['schedule:read']
        ]);
    }

    #[Route('/generate', name: 'api_schedules_generate', methods: ['POST'])]
    public function generate(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['sectionId'])) {
            return $this->json(['error' => 'Section ID required'], Response::HTTP_BAD_REQUEST);
        }

        $section = $this->sectionRepository->find($data['sectionId']);
        if (!$section) {
            return $this->json(['error' => 'Section not found'], Response::HTTP_NOT_FOUND);
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
            
            return $this->json([
                'message' => 'Schedule generated successfully',
                'schedules' => $schedules,
                'count' => count($schedules),
                'errors' => $errors
            ], Response::HTTP_CREATED, [], [
                'groups' => ['schedule:read']
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Failed to generate schedule',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
