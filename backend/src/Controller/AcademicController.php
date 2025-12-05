<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Academic\Query\GetAvailableSectionsQuery;
use App\Application\Academic\Query\GetGradeByIdQuery;
use App\Application\Academic\Query\GetGradesByLevelQuery;
use App\Application\Academic\Query\GetGradesQuery;
use App\Application\Academic\Query\GetSectionByIdQuery;
use App\Application\Academic\Query\GetSectionsQuery;
use App\Application\Academic\Query\GetSubjectByCodeQuery;
use App\Application\Academic\Query\GetSubjectByIdQuery;
use App\Application\Academic\Query\GetSubjectsQuery;
use App\Application\Academic\Query\SearchSubjectsQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class AcademicController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $queryBus
    ) {}

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
        $grades = $this->handleQuery(new GetGradesQuery());
        return $this->respond($grades, 200, ['grade:read']);
    }

    #[Route('/grades/{id}', name: 'api_grades_show', methods: ['GET'])]
    public function gradeShow(int $id): JsonResponse
    {
        $grade = $this->handleQuery(new GetGradeByIdQuery($id));
        if (!$grade) return $this->respondNotFound('Grade not found');
        return $this->respond($grade, 200, ['grade:read']);
    }

    #[Route('/grades/level/{level}', name: 'api_grades_by_level', methods: ['GET'])]
    public function gradesByLevel(string $level): JsonResponse
    {
        $grades = $this->handleQuery(new GetGradesByLevelQuery($level));
        return $this->respond($grades, 200, ['grade:read']);
    }

    // ===== SECTIONS =====
    #[Route('/sections', name: 'api_sections_index', methods: ['GET'])]
    public function sections(Request $request): JsonResponse
    {
        $gradeId = $request->query->getInt('gradeId', 0) ?: null;
        $year = $request->query->getInt('year', 0) ?: null;

        $sections = $this->handleQuery(new GetSectionsQuery($gradeId, $year));
        return $this->respond($sections, 200, ['section:read']);
    }

    #[Route('/sections/{id}', name: 'api_sections_show', methods: ['GET'])]
    public function sectionShow(int $id): JsonResponse
    {
        $result = $this->handleQuery(new GetSectionByIdQuery($id));
        if (!$result) return $this->respondNotFound('Section not found');

        return $this->respond($result, 200, ['section:read']);
    }

    #[Route('/sections/available', name: 'api_sections_available', methods: ['GET'])]
    public function availableSections(Request $request): JsonResponse
    {
        $year = $request->query->getInt('year', 0) ?: null;
        $sections = $this->handleQuery(new GetAvailableSectionsQuery($year));
        return $this->respond($sections, 200, ['section:read']);
    }

    // ===== SUBJECTS =====
    #[Route('/subjects', name: 'api_subjects_index', methods: ['GET'])]
    public function subjects(): JsonResponse
    {
        $subjects = $this->handleQuery(new GetSubjectsQuery());
        return $this->respond($subjects, 200, ['subject:read']);
    }

    #[Route('/subjects/{id}', name: 'api_subjects_show', methods: ['GET'])]
    public function subjectShow(int $id): JsonResponse
    {
        $subject = $this->handleQuery(new GetSubjectByIdQuery($id));
        if (!$subject) return $this->respondNotFound('Subject not found');
        return $this->respond($subject, 200, ['subject:read']);
    }

    #[Route('/subjects/search', name: 'api_subjects_search', methods: ['GET'])]
    public function subjectSearch(Request $request): JsonResponse
    {
        $query = trim($request->query->get('q', ''));
        if (!$query) return $this->respondBadRequest('Search query required');

        $subjects = $this->handleQuery(new SearchSubjectsQuery($query));
        return $this->respond($subjects, 200, ['subject:read']);
    }

    #[Route('/subjects/code/{code}', name: 'api_subjects_by_code', methods: ['GET'])]
    public function subjectByCode(string $code): JsonResponse
    {
        $subject = $this->handleQuery(new GetSubjectByCodeQuery($code));
        if (!$subject) return $this->respondNotFound('Subject not found');
        return $this->respond($subject, 200, ['subject:read']);
    }

    private function handleQuery(object $query): mixed
    {
        $envelope = $this->queryBus->dispatch($query);
        $handledStamp = $envelope->last(HandledStamp::class);
        return $handledStamp?->getResult();
    }
}
