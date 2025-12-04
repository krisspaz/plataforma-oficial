<?php

namespace App\Controller;

use App\Repository\GradeRepository;
use App\Repository\SectionRepository;
use App\Repository\SubjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class AcademicController extends AbstractController
{
    public function __construct(
        private GradeRepository $gradeRepository,
        private SectionRepository $sectionRepository,
        private SubjectRepository $subjectRepository
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

    // ===== GRADES =====
    #[Route('/grades', name: 'api_grades_index', methods: ['GET'])]
    public function grades(): JsonResponse
    {
        return $this->respond($this->gradeRepository->findAll(), 200, ['grade:read']);
    }

    #[Route('/grades/{id}', name: 'api_grades_show', methods: ['GET'])]
    public function gradeShow(int $id): JsonResponse
    {
        $grade = $this->gradeRepository->find($id);
        if (!$grade) return $this->respondNotFound('Grade not found');
        return $this->respond($grade, 200, ['grade:read']);
    }

    #[Route('/grades/level/{level}', name: 'api_grades_by_level', methods: ['GET'])]
    public function gradesByLevel(string $level): JsonResponse
    {
        return $this->respond($this->gradeRepository->findByLevel($level), 200, ['grade:read']);
    }

    // ===== SECTIONS =====
    #[Route('/sections', name: 'api_sections_index', methods: ['GET'])]
    public function sections(Request $request): JsonResponse
    {
        $gradeId = $request->query->getInt('gradeId', 0);
        $year = $request->query->getInt('year', (int) date('Y'));

        $sections = $gradeId 
            ? $this->sectionRepository->findByGradeAndYear($gradeId, $year) 
            : $this->sectionRepository->findAll();

        return $this->respond($sections, 200, ['section:read']);
    }

    #[Route('/sections/{id}', name: 'api_sections_show', methods: ['GET'])]
    public function sectionShow(int $id): JsonResponse
    {
        $section = $this->sectionRepository->find($id);
        if (!$section) return $this->respondNotFound('Section not found');

        return $this->respond([
            'section' => $section,
            'currentEnrollment' => $section->getCurrentEnrollmentCount(),
            'hasSpace' => $section->hasAvailableSpace()
        ], 200, ['section:read']);
    }

    #[Route('/sections/available', name: 'api_sections_available', methods: ['GET'])]
    public function availableSections(Request $request): JsonResponse
    {
        $year = $request->query->getInt('year', (int) date('Y'));
        return $this->respond($this->sectionRepository->findWithAvailableSpace($year), 200, ['section:read']);
    }

    // ===== SUBJECTS =====
    #[Route('/subjects', name: 'api_subjects_index', methods: ['GET'])]
    public function subjects(): JsonResponse
    {
        return $this->respond($this->subjectRepository->findAll(), 200, ['subject:read']);
    }

    #[Route('/subjects/{id}', name: 'api_subjects_show', methods: ['GET'])]
    public function subjectShow(int $id): JsonResponse
    {
        $subject = $this->subjectRepository->find($id);
        if (!$subject) return $this->respondNotFound('Subject not found');
        return $this->respond($subject, 200, ['subject:read']);
    }

    #[Route('/subjects/search', name: 'api_subjects_search', methods: ['GET'])]
    public function subjectSearch(Request $request): JsonResponse
    {
        $query = trim($request->query->get('q', ''));
        if (!$query) return $this->respondBadRequest('Search query required');

        return $this->respond($this->subjectRepository->search($query), 200, ['subject:read']);
    }

    #[Route('/subjects/code/{code}', name: 'api_subjects_by_code', methods: ['GET'])]
    public function subjectByCode(string $code): JsonResponse
    {
        $subject = $this->subjectRepository->findOneByCode($code);
        if (!$subject) return $this->respondNotFound('Subject not found');
        return $this->respond($subject, 200, ['subject:read']);
    }
}
