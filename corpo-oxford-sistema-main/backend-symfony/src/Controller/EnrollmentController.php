<?php

namespace App\Controller;

use App\Entity\Enrollment;
use App\Entity\Student;
use App\Entity\Grade;
use App\Entity\Section;
use App\Repository\EnrollmentRepository;
use App\Repository\StudentRepository;
use App\Repository\SectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/enrollments')]
class EnrollmentController extends AbstractController
{
    public function __construct(
        private EnrollmentRepository $enrollmentRepository,
        private StudentRepository $studentRepository,
        private SectionRepository $sectionRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('', name: 'api_enrollments_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $year = $request->query->getInt('year', (int) date('Y'));
        $enrollments = $this->enrollmentRepository->findActiveByYear($year);
        
        return $this->json($enrollments, Response::HTTP_OK, [], [
            'groups' => ['enrollment:read']
        ]);
    }

    #[Route('/{id}', name: 'api_enrollments_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $enrollment = $this->enrollmentRepository->find($id);
        
        if (!$enrollment) {
            return $this->json(['error' => 'Enrollment not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($enrollment, Response::HTTP_OK, [], [
            'groups' => ['enrollment:read']
        ]);
    }

    #[Route('', name: 'api_enrollments_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['studentId']) || !isset($data['sectionId'])) {
            return $this->json([
                'error' => 'Student ID and Section ID are required'
            ], Response::HTTP_BAD_REQUEST);
        }

        $student = $this->studentRepository->find($data['studentId']);
        if (!$student) {
            return $this->json(['error' => 'Student not found'], Response::HTTP_NOT_FOUND);
        }

        $section = $this->sectionRepository->find($data['sectionId']);
        if (!$section) {
            return $this->json(['error' => 'Section not found'], Response::HTTP_NOT_FOUND);
        }

        // Check if section has available space
        if (!$section->hasAvailableSpace()) {
            return $this->json([
                'error' => 'Section is full',
                'capacity' => $section->getCapacity(),
                'current' => $section->getCurrentEnrollmentCount()
            ], Response::HTTP_BAD_REQUEST);
        }

        // Check if student is already enrolled this year
        $existingEnrollments = $this->enrollmentRepository->findBy([
            'student' => $student,
            'academicYear' => $section->getAcademicYear(),
            'status' => 'active'
        ]);

        if (count($existingEnrollments) > 0) {
            return $this->json([
                'error' => 'Student is already enrolled for this academic year'
            ], Response::HTTP_BAD_REQUEST);
        }

        $enrollment = new Enrollment();
        $enrollment->setStudent($student);
        $enrollment->setGrade($section->getGrade());
        $enrollment->setSection($section);
        $enrollment->setAcademicYear($section->getAcademicYear());
        $enrollment->setStatus('active');
        $enrollment->setEnrollmentDate(new \DateTime());

        $this->entityManager->persist($enrollment);
        $this->entityManager->flush();

        return $this->json($enrollment, Response::HTTP_CREATED, [], [
            'groups' => ['enrollment:read']
        ]);
    }

    #[Route('/{id}', name: 'api_enrollments_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $enrollment = $this->enrollmentRepository->find($id);
        
        if (!$enrollment) {
            return $this->json(['error' => 'Enrollment not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['status'])) {
            $enrollment->setStatus($data['status']);
        }

        if (isset($data['sectionId'])) {
            $section = $this->sectionRepository->find($data['sectionId']);
            if ($section) {
                $enrollment->setSection($section);
                $enrollment->setGrade($section->getGrade());
            }
        }

        $this->entityManager->flush();

        return $this->json($enrollment, Response::HTTP_OK, [], [
            'groups' => ['enrollment:read']
        ]);
    }

    #[Route('/{id}/cancel', name: 'api_enrollments_cancel', methods: ['POST'])]
    public function cancel(int $id): JsonResponse
    {
        $enrollment = $this->enrollmentRepository->find($id);
        
        if (!$enrollment) {
            return $this->json(['error' => 'Enrollment not found'], Response::HTTP_NOT_FOUND);
        }

        $enrollment->setStatus('cancelled');
        $this->entityManager->flush();

        return $this->json([
            'message' => 'Enrollment cancelled successfully',
            'enrollment' => $enrollment
        ], Response::HTTP_OK, [], [
            'groups' => ['enrollment:read']
        ]);
    }

    #[Route('/student/{studentId}', name: 'api_enrollments_by_student', methods: ['GET'])]
    public function byStudent(int $studentId): JsonResponse
    {
        $enrollments = $this->enrollmentRepository->findByStudent($studentId);
        
        return $this->json($enrollments, Response::HTTP_OK, [], [
            'groups' => ['enrollment:read']
        ]);
    }

    #[Route('/stats/by-grade', name: 'api_enrollments_stats_grade', methods: ['GET'])]
    public function statsByGrade(Request $request): JsonResponse
    {
        $year = $request->query->getInt('year', (int) date('Y'));
        $stats = $this->enrollmentRepository->getStatsByGrade($year);
        
        return $this->json($stats);
    }
}
