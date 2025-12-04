<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Application\Coordination\Command\AssignTeacherCommand;
use App\Application\Coordination\Command\CreateAnnouncementCommand;
use App\Application\Coordination\Command\CreateCalendarEventCommand;
use App\Application\Coordination\Query\GetAnnouncementsQuery;
use App\Application\Coordination\Query\GetCalendarEventsQuery;
use App\Application\Coordination\Query\GetTeacherAssignmentsQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/coordination')]
class CoordinationController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {}

    /**
     * Assign a teacher to a subject/grade/section.
     */
    #[Route('/assignments', name: 'api_coordination_assign', methods: ['POST'])]
    #[IsGranted('ROLE_COORDINATOR')]
    public function assignTeacher(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $command = new AssignTeacherCommand(
                teacherId: $data['teacher_id'],
                subjectId: $data['subject_id'],
                gradeId: $data['grade_id'],
                sectionId: $data['section_id'],
                academicYear: $data['academic_year'] ?? (int) date('Y')
            );

            $this->messageBus->dispatch($command);

            return $this->json([
                'success' => true,
                'message' => 'Teacher assigned successfully'
            ], Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'error' => 'Error assigning teacher'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get assignments for a teacher.
     */
    #[Route('/assignments/teacher/{teacherId}', name: 'api_coordination_get_assignments', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getTeacherAssignments(int $teacherId, Request $request): JsonResponse
    {
        $query = new GetTeacherAssignmentsQuery(
            teacherId: $teacherId,
            academicYear: $request->query->getInt('year') ?: null
        );

        $envelope = $this->messageBus->dispatch($query);
        $handledStamp = $envelope->last(HandledStamp::class);
        $assignments = $handledStamp->getResult();

        return $this->json([
            'success' => true,
            'data' => $assignments
        ]);
    }

    /**
     * Create an announcement.
     */
    #[Route('/announcements', name: 'api_coordination_create_announcement', methods: ['POST'])]
    #[IsGranted('ROLE_COORDINATOR')]
    public function createAnnouncement(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $user = $this->getUser();

            $command = new CreateAnnouncementCommand(
                title: $data['title'],
                content: $data['content'],
                type: $data['type'],
                authorId: $user->getId(),
                targetIds: $data['target_ids'] ?? null,
                expiresAt: $data['expires_at'] ?? null
            );

            $this->messageBus->dispatch($command);

            return $this->json([
                'success' => true,
                'message' => 'Announcement created successfully'
            ], Response::HTTP_CREATED);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'error' => 'Error creating announcement'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get announcements.
     */
    #[Route('/announcements', name: 'api_coordination_get_announcements', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getAnnouncements(Request $request): JsonResponse
    {
        $query = new GetAnnouncementsQuery(
            type: $request->query->get('type')
        );

        $envelope = $this->messageBus->dispatch($query);
        $handledStamp = $envelope->last(HandledStamp::class);
        $announcements = $handledStamp->getResult();

        return $this->json([
            'success' => true,
            'data' => $announcements
        ]);
    }

    /**
     * Create a calendar event.
     */
    #[Route('/calendar', name: 'api_coordination_create_event', methods: ['POST'])]
    #[IsGranted('ROLE_COORDINATOR')]
    public function createCalendarEvent(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $command = new CreateCalendarEventCommand(
                title: $data['title'],
                startDate: $data['start_date'],
                endDate: $data['end_date'],
                type: $data['type'],
                academicYear: $data['academic_year'] ?? (int) date('Y'),
                isAllDay: $data['is_all_day'] ?? false,
                description: $data['description'] ?? null
            );

            $this->messageBus->dispatch($command);

            return $this->json([
                'success' => true,
                'message' => 'Event created successfully'
            ], Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'error' => 'Error creating event'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get calendar events.
     */
    #[Route('/calendar', name: 'api_coordination_get_events', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getCalendarEvents(Request $request): JsonResponse
    {
        $startDate = $request->query->get('start_date', date('Y-m-01'));
        $endDate = $request->query->get('end_date', date('Y-m-t'));

        $query = new GetCalendarEventsQuery(
            startDate: $startDate,
            endDate: $endDate
        );

        $envelope = $this->messageBus->dispatch($query);
        $handledStamp = $envelope->last(HandledStamp::class);
        $events = $handledStamp->getResult();

        return $this->json([
            'success' => true,
            'data' => $events
        ]);
    }
}
