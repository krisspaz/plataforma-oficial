<?php

namespace App\Controller;

use App\Repository\TeacherRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/teachers')]
class TeacherController extends AbstractController
{
    public function __construct(
        private TeacherRepository $teacherRepository,
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('', name: 'api_teachers_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $teachers = $this->teacherRepository->findAll();
        
        return $this->json($teachers, Response::HTTP_OK, [], [
            'groups' => ['teacher:read']
        ]);
    }

    #[Route('/{id}', name: 'api_teachers_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $teacher = $this->teacherRepository->find($id);
        
        if (!$teacher) {
            return $this->json(['error' => 'Teacher not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($teacher, Response::HTTP_OK, [], [
            'groups' => ['teacher:read']
        ]);
    }

    #[Route('/search', name: 'api_teachers_search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        
        if (empty($query)) {
            return $this->json(['error' => 'Search query required'], Response::HTTP_BAD_REQUEST);
        }

        $teachers = $this->teacherRepository->search($query);
        
        return $this->json($teachers, Response::HTTP_OK, [], [
            'groups' => ['teacher:read']
        ]);
    }

    #[Route('/specialization/{specialization}', name: 'api_teachers_by_specialization', methods: ['GET'])]
    public function bySpecialization(string $specialization): JsonResponse
    {
        $teachers = $this->teacherRepository->findBySpecialization($specialization);
        
        return $this->json($teachers, Response::HTTP_OK, [], [
            'groups' => ['teacher:read']
        ]);
    }

    #[Route('/birthdays/month', name: 'api_teachers_birthdays', methods: ['GET'])]
    public function birthdaysThisMonth(): JsonResponse
    {
        $teachers = $this->teacherRepository->findBirthdaysThisMonth();
        
        return $this->json([
            'teachers' => $teachers,
            'count' => count($teachers)
        ], Response::HTTP_OK, [], [
            'groups' => ['teacher:read']
        ]);
    }
}
