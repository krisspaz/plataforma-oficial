<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Application\Student\Command\CreateStudentCommandHandler;
use App\Application\Student\Query\GetStudentByIdQueryHandler;
use App\Application\Student\Query\GetAllStudentsQueryHandler;
use App\Application\Student\DTO\CreateStudentDTO;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/v2/students')]
final class StudentControllerV2 extends AbstractController
{
    public function __construct(
        private readonly CreateStudentCommandHandler $createStudentHandler,
        private readonly GetStudentByIdQueryHandler $getStudentByIdHandler,
        private readonly GetAllStudentsQueryHandler $getAllStudentsHandler,
        private readonly SerializerInterface $serializer,
    ) {}

    #[Route('', name: 'api_v2_students_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $dto = new CreateStudentDTO(
                email: $data['email'] ?? '',
                firstName: $data['firstName'] ?? '',
                lastName: $data['lastName'] ?? '',
                phone: $data['phone'] ?? null,
                birthDate: $data['birthDate'] ?? null,
                gender: $data['gender'] ?? null,
                nationality: $data['nationality'] ?? null,
                address: $data['address'] ?? null,
                emergencyContact: $data['emergencyContact'] ?? null,
            );

            $student = $this->createStudentHandler->handle($dto);

            return $this->json($student, Response::HTTP_CREATED, [], [
                'groups' => ['student:read']
            ]);
        } catch (\DomainException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], Response::HTTP_CONFLICT);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'An error occurred while creating the student'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/{id}', name: 'api_v2_students_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try {
            $student = $this->getStudentByIdHandler->handle($id);

            if (!$student) {
                return $this->json([
                    'error' => 'Student not found'
                ], Response::HTTP_NOT_FOUND);
            }

            return $this->json($student, Response::HTTP_OK, [], [
                'groups' => ['student:read']
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('', name: 'api_v2_students_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $perPage = $request->query->getInt('perPage', 20);

        $result = $this->getAllStudentsHandler->handle($page, $perPage);

        return $this->json($result, Response::HTTP_OK, [], [
            'groups' => ['student:read']
        ]);
    }
}
