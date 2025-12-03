<?php

namespace App\Controller;

use App\Repository\GradeRepository;
use App\Repository\SectionRepository;
use App\Repository\SubjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class AcademicController extends AbstractController
{
    public function __construct(
        private GradeRepository $gradeRepository,
        private SectionRepository $sectionRepository,
        private SubjectRepository $subjectRepository
    ) {
    }

    // ===== GRADES =====
    
    #[Route('/grades', name: 'api_grades_index', methods: ['GET'])]
    public function grades(): JsonResponse
    {
        $grades = $this->gradeRepository->findAll();
        
        return $this->json($grades, Response::HTTP_OK, [], [
            'groups' => ['grade:read']
        ]);
    }

    #[Route('/grades/{id}', name: 'api_grades_show', methods: ['GET'])]
    public function gradeShow(int $id): JsonResponse
    {
        $grade = $this->gradeRepository->find($id);
        
        if (!$grade) {
            return $this->json(['error' => 'Grade not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($grade, Response::HTTP_OK, [], [
            'groups' => ['grade:read']
        ]);
    }

    #[Route('/grades/level/{level}', name: 'api_grades_by_level', methods: ['GET'])]
    public function gradesByLevel(string $level): JsonResponse
    {
        $grades = $this->gradeRepository->findByLevel($level);
        
        return $this->json($grades, Response::HTTP_OK, [], [
            'groups' => ['grade:read']
        ]);
    }

    // ===== SECTIONS =====
    
    #[Route('/sections', name: 'api_sections_index', methods: ['GET'])]
    public function sections(Request $request): JsonResponse
    {
        $gradeId = $request->query->getInt('gradeId');
        $year = $request->query->getInt('year', (int) date('Y'));
        
        if ($gradeId) {
            $sections = $this->sectionRepository->findByGradeAndYear($gradeId, $year);
        } else {
            $sections = $this->sectionRepository->findAll();
        }
        
        return $this->json($sections, Response::HTTP_OK, [], [
            'groups' => ['section:read']
        ]);
    }

    #[Route('/sections/{id}', name: 'api_sections_show', methods: ['GET'])]
    public function sectionShow(int $id): JsonResponse
    {
        $section = $this->sectionRepository->find($id);
        
        if (!$section) {
            return $this->json(['error' => 'Section not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'section' => $section,
            'currentEnrollment' => $section->getCurrentEnrollmentCount(),
            'hasSpace' => $section->hasAvailableSpace()
        ], Response::HTTP_OK, [], [
            'groups' => ['section:read']
        ]);
    }

    #[Route('/sections/available', name: 'api_sections_available', methods: ['GET'])]
    public function availableSections(Request $request): JsonResponse
    {
        $year = $request->query->getInt('year', (int) date('Y'));
        $sections = $this->sectionRepository->findWithAvailableSpace($year);
        
        return $this->json($sections, Response::HTTP_OK, [], [
            'groups' => ['section:read']
        ]);
    }

    // ===== SUBJECTS =====
    
    #[Route('/subjects', name: 'api_subjects_index', methods: ['GET'])]
    public function subjects(): JsonResponse
    {
        $subjects = $this->subjectRepository->findAll();
        
        return $this->json($subjects, Response::HTTP_OK, [], [
            'groups' => ['subject:read']
        ]);
    }

    #[Route('/subjects/{id}', name: 'api_subjects_show', methods: ['GET'])]
    public function subjectShow(int $id): JsonResponse
    {
        $subject = $this->subjectRepository->find($id);
        
        if (!$subject) {
            return $this->json(['error' => 'Subject not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($subject, Response::HTTP_OK, [], [
            'groups' => ['subject:read']
        ]);
    }

    #[Route('/subjects/search', name: 'api_subjects_search', methods: ['GET'])]
    public function subjectSearch(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        
        if (empty($query)) {
            return $this->json(['error' => 'Search query required'], Response::HTTP_BAD_REQUEST);
        }

        $subjects = $this->subjectRepository->search($query);
        
        return $this->json($subjects, Response::HTTP_OK, [], [
            'groups' => ['subject:read']
        ]);
    }

    #[Route('/subjects/code/{code}', name: 'api_subjects_by_code', methods: ['GET'])]
    public function subjectByCode(string $code): JsonResponse
    {
        $subject = $this->subjectRepository->findOneByCode($code);
        
        if (!$subject) {
            return $this->json(['error' => 'Subject not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($subject, Response::HTTP_OK, [], [
            'groups' => ['subject:read']
        ]);
    }
}
