<?php

namespace App\Controller;

use App\Entity\Enrollment;
use App\Repository\EnrollmentRepository;
use App\Repository\StudentRepository;
use App\Repository\SectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Traits\ApiResponseTrait;

#[Route('/api/enrollments')]
class EnrollmentController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private EnrollmentRepository $enrollmentRepository,
        private StudentRepository $studentRepository,
        private SectionRepository $sectionRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('', name: 'api_enrollments_index', methods: ['GET'])]
    public function index(Request $request)
    {
        $year = $request->query->getInt('year', (int) date('Y'));
        $enrollments = $this->enrollmentRepository->findActiveByYear($year);

        return $this->success($enrollments);
    }

    #[Route('/{id}', name: 'api_enrollments_show', methods: ['GET'])]
    public function show(int $id)
    {
        $enrollment = $this->enrollmentRepository->find($id);

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

        $student = $this->studentRepository->find($data['studentId']);
        if (!$student) return $this->error('Student not found', Response::HTTP_NOT_FOUND);

        $section = $this->sectionRepository->find($data['sectionId']);
        if (!$section) return $this->error('Section not found', Response::HTTP_NOT_FOUND);

        if (!$section->hasAvailableSpace()) {
            return $this->error('Section is full', Response::HTTP_BAD_REQUEST, [
                'capacity' => $section->getCapacity(),
                'current' => $section->getCurrentEnrollmentCount()
            ]);
        }

        $existingEnrollments = $this->enrollmentRepository->findBy([
            'student' => $student,
            'academicYear' => $section->getAcademicYear(),
            'status' => 'active'
        ]);

        if (!empty($existingEnrollments)) {
            return $this->error('Student is already enrolled for this academic year', Response::HTTP_BAD_REQUEST);
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

        return $this->success($enrollment, Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_enrollments_update', methods: ['PUT'])]
    public function update(int $id, Request $request)
    {
        $enrollment = $this->enrollmentRepository->find($id);
        if (!$enrollment) return $this->error('Enrollment not found', Response::HTTP_NOT_FOUND);

        $data = json_decode($request->getContent(), true);

        if (isset($data['status'])) $enrollment->setStatus($data['status']);

        if (!empty($data['sectionId'])) {
            $section = $this->sectionRepository->find($data['sectionId']);
            if ($section) {
                $enrollment->setSection($section);
                $enrollment->setGrade($section->getGrade());
            }
        }

        $this->entityManager->flush();

        return $this->success($enrollment);
    }

    #[Route('/{id}/cancel', name: 'api_enrollments_cancel', methods: ['POST'])]
    public function cancel(int $id)
    {
        $enrollment = $this->enrollmentRepository->find($id);
        if (!$enrollment) return $this->error('Enrollment not found', Response::HTTP_NOT_FOUND);

        $enrollment->setStatus('cancelled');
        $this->entityManager->flush();

        return $this->success([
            'message' => 'Enrollment cancelled successfully',
            'enrollment' => $enrollment
        ]);
    }

    #[Route('/student/{studentId}', name: 'api_enrollments_by_student', methods: ['GET'])]
    public function byStudent(int $studentId)
    {
        $enrollments = $this->enrollmentRepository->findByStudent($studentId);

        return $this->success($enrollments);
    }

    #[Route('/stats/by-grade', name: 'api_enrollments_stats_grade', methods: ['GET'])]
    public function statsByGrade(Request $request)
    {
        $year = $request->query->getInt('year', (int) date('Y'));
        $stats = $this->enrollmentRepository->getStatsByGrade($year);

        return $this->success($stats);
    }
}
