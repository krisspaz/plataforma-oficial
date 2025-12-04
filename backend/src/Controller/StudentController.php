<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Traits\ApiResponseTrait;
use App\Entity\Student;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/api/students')]
#[OA\Tag(name: 'Students')]
class StudentController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private readonly StudentRepository $studentRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'api_students_index', methods: ['GET'])]
    #[OA\Get(path: '/api/students', summary: 'List all students')]
    public function index(): JsonResponse
    {
        $students = $this->studentRepository->findAll();
        
        return $this->success($students, 200, [], ['student:read']);
    }

    #[Route('/{id}', name: 'api_students_show', methods: ['GET'])]
    #[OA\Get(path: '/api/students/{id}', summary: 'Get student details')]
    public function show(int $id): JsonResponse
    {
        $student = $this->studentRepository->find($id);
        
        if (!$student) {
            return $this->notFound('Student');
        }

        return $this->success($student, 200, [], ['student:read']);
    }

    #[Route('/search', name: 'api_students_search', methods: ['GET'])]
    #[OA\Get(path: '/api/students/search', summary: 'Search students')]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        
        if (empty($query)) {
            return $this->validationError(['q' => 'Search query required']);
        }

        $students = $this->studentRepository->search($query);
        
        return $this->success($students, 200, [], ['student:read']);
    }

    #[Route('/grade/{gradeId}', name: 'api_students_by_grade', methods: ['GET'])]
    #[OA\Get(path: '/api/students/grade/{gradeId}', summary: 'Get students by grade')]
    public function byGrade(int $gradeId): JsonResponse
    {
        $students = $this->studentRepository->findByGrade($gradeId);
        
        return $this->success($students, 200, [], ['student:read']);
    }

    #[Route('/section/{sectionId}', name: 'api_students_by_section', methods: ['GET'])]
    #[OA\Get(path: '/api/students/section/{sectionId}', summary: 'Get students by section')]
    public function bySection(int $sectionId): JsonResponse
    {
        $students = $this->studentRepository->findBySection($sectionId);
        
        return $this->success($students, 200, [], ['student:read']);
    }

    #[Route('/stats/gender', name: 'api_students_stats_gender', methods: ['GET'])]
    #[OA\Get(path: '/api/students/stats/gender', summary: 'Get student gender statistics')]
    public function statsByGender(): JsonResponse
    {
        $stats = $this->studentRepository->getStatsByGender();
        
        return $this->success($stats);
    }
}
