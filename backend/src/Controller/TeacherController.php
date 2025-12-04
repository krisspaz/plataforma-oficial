<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Traits\ApiResponseTrait;
use App\Repository\TeacherRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/api/teachers')]
#[OA\Tag(name: 'Teachers')]
class TeacherController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private readonly TeacherRepository $teacherRepository,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'api_teachers_index', methods: ['GET'])]
    #[OA\Get(path: '/api/teachers', summary: 'List all teachers')]
    public function index(): JsonResponse
    {
        $teachers = $this->teacherRepository->findAll();
        
        return $this->success($teachers, 200, [], ['teacher:read']);
    }

    #[Route('/{id}', name: 'api_teachers_show', methods: ['GET'])]
    #[OA\Get(path: '/api/teachers/{id}', summary: 'Get teacher details')]
    public function show(int $id): JsonResponse
    {
        $teacher = $this->teacherRepository->find($id);
        
        if (!$teacher) {
            return $this->notFound('Teacher');
        }

        return $this->success($teacher, 200, [], ['teacher:read']);
    }

    #[Route('/search', name: 'api_teachers_search', methods: ['GET'])]
    #[OA\Get(path: '/api/teachers/search', summary: 'Search teachers')]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        
        if (empty($query)) {
            return $this->validationError(['q' => 'Search query required']);
        }

        $teachers = $this->teacherRepository->search($query);
        
        return $this->success($teachers, 200, [], ['teacher:read']);
    }

    #[Route('/specialization/{specialization}', name: 'api_teachers_by_specialization', methods: ['GET'])]
    #[OA\Get(path: '/api/teachers/specialization/{specialization}', summary: 'Get teachers by specialization')]
    public function bySpecialization(string $specialization): JsonResponse
    {
        $teachers = $this->teacherRepository->findBySpecialization($specialization);
        
        return $this->success($teachers, 200, [], ['teacher:read']);
    }

    #[Route('/birthdays/month', name: 'api_teachers_birthdays', methods: ['GET'])]
    #[OA\Get(path: '/api/teachers/birthdays/month', summary: 'Get teachers with birthdays this month')]
    public function birthdaysThisMonth(): JsonResponse
    {
        $teachers = $this->teacherRepository->findBirthdaysThisMonth();
        
        return $this->success([
            'teachers' => $teachers,
            'count' => count($teachers)
        ], 200, [], ['teacher:read']);
    }
}
