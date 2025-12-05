<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Teacher\Query\GetTeacherBirthdaysQuery;
use App\Application\Teacher\Query\GetTeacherByIdQuery;
use App\Application\Teacher\Query\GetTeachersBySpecializationQuery;
use App\Application\Teacher\Query\GetTeachersQuery;
use App\Application\Teacher\Query\SearchTeachersQuery;
use App\Controller\Traits\ApiResponseTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/api/teachers')]
#[OA\Tag(name: 'Teachers')]
class TeacherController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private readonly MessageBusInterface $queryBus
    ) {}

    #[Route('', name: 'api_teachers_index', methods: ['GET'])]
    #[OA\Get(path: '/api/teachers', summary: 'List all teachers')]
    public function index(): JsonResponse
    {
        $teachers = $this->handleQuery(new GetTeachersQuery());

        return $this->success($teachers, 200, [], ['teacher:read']);
    }

    #[Route('/{id}', name: 'api_teachers_show', methods: ['GET'])]
    #[OA\Get(path: '/api/teachers/{id}', summary: 'Get teacher details')]
    public function show(int $id): JsonResponse
    {
        $teacher = $this->handleQuery(new GetTeacherByIdQuery($id));

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

        $teachers = $this->handleQuery(new SearchTeachersQuery($query));

        return $this->success($teachers, 200, [], ['teacher:read']);
    }

    #[Route('/specialization/{specialization}', name: 'api_teachers_by_specialization', methods: ['GET'])]
    #[OA\Get(path: '/api/teachers/specialization/{specialization}', summary: 'Get teachers by specialization')]
    public function bySpecialization(string $specialization): JsonResponse
    {
        $teachers = $this->handleQuery(new GetTeachersBySpecializationQuery($specialization));

        return $this->success($teachers, 200, [], ['teacher:read']);
    }

    #[Route('/birthdays/month', name: 'api_teachers_birthdays', methods: ['GET'])]
    #[OA\Get(path: '/api/teachers/birthdays/month', summary: 'Get teachers with birthdays this month')]
    public function birthdaysThisMonth(): JsonResponse
    {
        $result = $this->handleQuery(new GetTeacherBirthdaysQuery());

        return $this->success($result, 200, [], ['teacher:read']);
    }

    /**
     * Handle a query and return the result
     */
    private function handleQuery(object $query): mixed
    {
        $envelope = $this->queryBus->dispatch($query);
        $handledStamp = $envelope->last(HandledStamp::class);

        return $handledStamp?->getResult();
    }
}
