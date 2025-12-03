<?php

namespace App\Controller;

use App\Entity\Student;
use App\Repository\StudentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/students')]
class StudentController extends AbstractController
{
    public function __construct(
        private StudentRepository $studentRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('', name: 'api_students_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $students = $this->studentRepository->findAll();
        
        return $this->json($students, Response::HTTP_OK, [], [
            'groups' => ['student:read']
        ]);
    }

    #[Route('/{id}', name: 'api_students_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $student = $this->studentRepository->find($id);
        
        if (!$student) {
            return $this->json(['error' => 'Student not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($student, Response::HTTP_OK, [], [
            'groups' => ['student:read']
        ]);
    }

    #[Route('/search', name: 'api_students_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        
        if (empty($query)) {
            return $this->json(['error' => 'Search query required'], Response::HTTP_BAD_REQUEST);
        }

        $students = $this->studentRepository->search($query);
        
        return $this->json($students, Response::HTTP_OK, [], [
            'groups' => ['student:read']
        ]);
    }

    #[Route('/grade/{gradeId}', name: 'api_students_by_grade', methods: ['GET'])]
    public function byGrade(int $gradeId): JsonResponse
    {
        $students = $this->studentRepository->findByGrade($gradeId);
        
        return $this->json($students, Response::HTTP_OK, [], [
            'groups' => ['student:read']
        ]);
    }

    #[Route('/section/{sectionId}', name: 'api_students_by_section', methods: ['GET'])]
    public function bySection(int $sectionId): JsonResponse
    {
        $students = $this->studentRepository->findBySection($sectionId);
        
        return $this->json($students, Response::HTTP_OK, [], [
            'groups' => ['student:read']
        ]);
    }

    #[Route('/stats/gender', name: 'api_students_stats_gender', methods: ['GET'])]
    public function statsByGender(): JsonResponse
    {
        $stats = $this->studentRepository->getStatsByGender();
        
        return $this->json($stats);
    }
}
