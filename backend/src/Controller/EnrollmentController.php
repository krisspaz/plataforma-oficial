<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Enrollment\Command\CancelEnrollmentCommand;
use App\Application\Enrollment\Command\CreateEnrollmentCommand;
use App\Application\Enrollment\Command\UpdateEnrollmentCommand;
use App\Application\Enrollment\Query\GetEnrollmentByIdQuery;
use App\Application\Enrollment\Query\GetEnrollmentsByStudentQuery;
use App\Application\Enrollment\Query\GetEnrollmentsQuery;
use App\Application\Enrollment\Query\GetEnrollmentStatsByGradeQuery;
use App\Controller\Traits\ApiResponseTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/enrollments')]
class EnrollmentController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private readonly MessageBusInterface $queryBus,
        private readonly MessageBusInterface $commandBus
    ) {}

    #[Route('', name: 'api_enrollments_index', methods: ['GET'])]
    public function index(Request $request)
    {
        $year = $request->query->getInt('year', (int) date('Y'));
        $enrollments = $this->handleQuery(new GetEnrollmentsQuery($year));

        return $this->success($enrollments);
    }

    #[Route('/{id}', name: 'api_enrollments_show', methods: ['GET'])]
    public function show(int $id)
    {
        $enrollment = $this->handleQuery(new GetEnrollmentByIdQuery($id));

        if (!$enrollment) {
            return $this->error('Enrollment not found', Response::HTTP_NOT_FOUND);
        }

        return $this->success($enrollment);
    }

    #[Route('', name: 'api_enrollments_create', methods: ['POST'])]
    public function create(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['studentId']) || empty($data['sectionId'])) {
            return $this->error('Student ID and Section ID are required', Response::HTTP_BAD_REQUEST);
        }

        $command = new CreateEnrollmentCommand(
            studentId: (int) $data['studentId'],
            sectionId: (int) $data['sectionId']
        );

        $result = $this->handleCommand($command);

        if (isset($result['error'])) {
            return $this->error($result['error'], $result['code'], $result['details'] ?? []);
        }

        return $this->success($result['enrollment'], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_enrollments_update', methods: ['PUT'])]
    public function update(int $id, Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $command = new UpdateEnrollmentCommand(
            enrollmentId: $id,
            status: $data['status'] ?? null,
            sectionId: isset($data['sectionId']) ? (int) $data['sectionId'] : null
        );

        $enrollment = $this->handleCommand($command);

        if (!$enrollment) {
            return $this->error('Enrollment not found', Response::HTTP_NOT_FOUND);
        }

        return $this->success($enrollment);
    }

    #[Route('/{id}/cancel', name: 'api_enrollments_cancel', methods: ['POST'])]
    public function cancel(int $id)
    {
        $enrollment = $this->handleCommand(new CancelEnrollmentCommand($id));

        if (!$enrollment) {
            return $this->error('Enrollment not found', Response::HTTP_NOT_FOUND);
        }

        return $this->success([
            'message' => 'Enrollment cancelled successfully',
            'enrollment' => $enrollment
        ]);
    }

    #[Route('/student/{studentId}', name: 'api_enrollments_by_student', methods: ['GET'])]
    public function byStudent(int $studentId)
    {
        $enrollments = $this->handleQuery(new GetEnrollmentsByStudentQuery($studentId));

        return $this->success($enrollments);
    }

    #[Route('/stats/by-grade', name: 'api_enrollments_stats_grade', methods: ['GET'])]
    public function statsByGrade(Request $request)
    {
        $year = $request->query->getInt('year', (int) date('Y'));
        $stats = $this->handleQuery(new GetEnrollmentStatsByGradeQuery($year));

        return $this->success($stats);
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

    /**
     * Handle a command and return the result
     */
    private function handleCommand(object $command): mixed
    {
        $envelope = $this->commandBus->dispatch($command);
        $handledStamp = $envelope->last(HandledStamp::class);

        return $handledStamp?->getResult();
    }
}
