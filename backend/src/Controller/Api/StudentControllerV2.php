<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Application\Student\Command\CreateStudentCommandHandler;
use App\Application\Student\Command\UpdateStudentCommandHandler;
use App\Application\Student\Command\DeleteStudentCommandHandler;
use App\Application\Student\Query\GetStudentByIdQueryHandler;
use App\Application\Student\Query\GetAllStudentsQueryHandler;
use App\Application\Student\DTO\CreateStudentDTO;
use App\Application\Student\DTO\UpdateStudentDTO;
use App\Controller\Traits\ApiResponseTrait;
use App\Domain\Exception\StudentNotFoundException;
use App\Domain\Exception\DuplicateStudentException;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;

#[Route('/api/v2/students')]
#[OA\Tag(name: 'Students V2')]
final class StudentControllerV2 extends AbstractController
{
    use ApiResponseTrait;

    private const MAX_PER_PAGE = 100;
    private const DEFAULT_PER_PAGE = 20;
    private const MIN_PER_PAGE = 1;

    public function __construct(
        private readonly CreateStudentCommandHandler $createStudentHandler,
        private readonly UpdateStudentCommandHandler $updateStudentHandler,
        private readonly DeleteStudentCommandHandler $deleteStudentHandler,
        private readonly GetStudentByIdQueryHandler $getStudentByIdHandler,
        private readonly GetAllStudentsQueryHandler $getAllStudentsHandler,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly LoggerInterface $logger,
    ) {}

    #[Route('', name: 'api_v2_students_create', methods: ['POST'])]
    #[OA\Post(
        summary: 'Crear un nuevo estudiante',
        description: 'Crea un nuevo estudiante en el sistema con la información proporcionada',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'firstName', 'lastName'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'student@example.com'),
                    new OA\Property(property: 'firstName', type: 'string', example: 'Juan'),
                    new OA\Property(property: 'lastName', type: 'string', example: 'Pérez'),
                    new OA\Property(property: 'phone', type: 'string', nullable: true, example: '+502 1234-5678'),
                    new OA\Property(property: 'birthDate', type: 'string', format: 'date', nullable: true, example: '2000-01-15'),
                    new OA\Property(property: 'gender', type: 'string', enum: ['M', 'F', 'O'], nullable: true, example: 'M'),
                    new OA\Property(property: 'nationality', type: 'string', nullable: true, example: 'Guatemalteco'),
                    new OA\Property(property: 'address', type: 'string', nullable: true, example: 'Zona 1, Ciudad de Guatemala'),
                    new OA\Property(property: 'emergencyContact', type: 'string', nullable: true, example: '+502 9876-5432'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Estudiante creado exitosamente',
                content: new OA\JsonContent(ref: new Model(type: CreateStudentDTO::class, groups: ['student:read']))
            ),
            new OA\Response(response: 400, description: 'Datos de entrada inválidos'),
            new OA\Response(response: 409, description: 'El estudiante ya existe'),
            new OA\Response(response: 500, description: 'Error interno del servidor'),
        ]
    )]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = $this->decodeJsonRequest($request);
            $dto = $this->createStudentDTOFromArray($data);

            // Validar DTO
            $errors = $this->validator->validate($dto);
            if (count($errors) > 0) {
                return $this->validationError($this->formatValidationErrors($errors));
            }

            $student = $this->createStudentHandler->handle($dto);

            $this->logger->info('Student created successfully', [
                'student_id' => $student->getId(),
                'email' => $student->getEmail()
            ]);

            return $this->success($student, 201, [], ['student:read']);

        } catch (DuplicateStudentException $e) {
            $this->logger->warning('Attempt to create duplicate student', [
                'email' => $data['email'] ?? null,
                'message' => $e->getMessage()
            ]);
            
            return $this->error('Student already exists', 409, $e->getMessage());

        } catch (\InvalidArgumentException $e) {
            return $this->error('Invalid input', 400, $e->getMessage());

        } catch (\JsonException $e) {
            return $this->error('Invalid JSON format', 400, $e->getMessage());

        } catch (\Throwable $e) {
            $this->logger->error('Error creating student', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->error(
                'An error occurred while creating the student', 
                500, 
                $this->getParameter('kernel.environment') === 'dev' ? $e->getMessage() : 'Please contact support'
            );
        }
    }

    #[Route('/{id}', name: 'api_v2_students_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    #[OA\Get(
        summary: 'Obtener un estudiante por ID',
        description: 'Retorna los detalles de un estudiante específico',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID del estudiante',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Estudiante encontrado',
                content: new OA\JsonContent(ref: new Model(type: CreateStudentDTO::class, groups: ['student:read']))
            ),
            new OA\Response(response: 404, description: 'Estudiante no encontrado'),
            new OA\Response(response: 400, description: 'ID inválido'),
        ]
    )]
    public function show(int $id): JsonResponse
    {
        try {
            if ($id <= 0) {
                return $this->error('Invalid student ID', 400, 'Student ID must be a positive integer');
            }

            $student = $this->getStudentByIdHandler->handle($id);

            if (!$student) {
                return $this->notFound('Student');
            }

            return $this->success($student, 200, [], ['student:read']);

        } catch (StudentNotFoundException $e) {
            return $this->notFound('Student');

        } catch (\Throwable $e) {
            $this->logger->error('Error fetching student', [
                'student_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->error('An error occurred while fetching the student', 500);
        }
    }

    #[Route('', name: 'api_v2_students_list', methods: ['GET'])]
    #[OA\Get(
        summary: 'Listar todos los estudiantes',
        description: 'Retorna una lista paginada de estudiantes',
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                description: 'Número de página',
                schema: new OA\Schema(type: 'integer', default: 1, minimum: 1, example: 1)
            ),
            new OA\Parameter(
                name: 'perPage',
                in: 'query',
                required: false,
                description: 'Elementos por página',
                schema: new OA\Schema(type: 'integer', default: 20, minimum: 1, maximum: 100, example: 20)
            ),
            new OA\Parameter(
                name: 'search',
                in: 'query',
                required: false,
                description: 'Buscar por nombre o email',
                schema: new OA\Schema(type: 'string', example: 'Juan')
            ),
            new OA\Parameter(
                name: 'sortBy',
                in: 'query',
                required: false,
                description: 'Campo para ordenar',
                schema: new OA\Schema(type: 'string', enum: ['id', 'firstName', 'lastName', 'email', 'createdAt'], default: 'id')
            ),
            new OA\Parameter(
                name: 'sortOrder',
                in: 'query',
                required: false,
                description: 'Orden de clasificación',
                schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], default: 'asc')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de estudiantes',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: new Model(type: CreateStudentDTO::class, groups: ['student:read']))),
                        new OA\Property(property: 'meta', properties: [
                            new OA\Property(property: 'currentPage', type: 'integer', example: 1),
                            new OA\Property(property: 'perPage', type: 'integer', example: 20),
                            new OA\Property(property: 'totalPages', type: 'integer', example: 5),
                            new OA\Property(property: 'totalItems', type: 'integer', example: 100),
                        ], type: 'object')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(response: 400, description: 'Parámetros de consulta inválidos'),
        ]
    )]
    public function list(Request $request): JsonResponse
    {
        try {
            $page = $request->query->getInt('page', 1);
            $perPage = $request->query->getInt('perPage', self::DEFAULT_PER_PAGE);
            $search = $request->query->get('search');
            $sortBy = $request->query->get('sortBy', 'id');
            $sortOrder = $request->query->get('sortOrder', 'asc');

            // Validar parámetros
            $validationErrors = $this->validateListParameters($page, $perPage, $sortBy, $sortOrder);
            if (!empty($validationErrors)) {
                return $this->validationError($validationErrors);
            }

            // Normalizar perPage
            $perPage = min($perPage, self::MAX_PER_PAGE);
            $perPage = max($perPage, self::MIN_PER_PAGE);

            $result = $this->getAllStudentsHandler->handle(
                page: $page,
                perPage: $perPage,
                search: $search,
                sortBy: $sortBy,
                sortOrder: $sortOrder
            );

            return $this->success($result, 200, [], ['student:read']);

        } catch (\InvalidArgumentException $e) {
            return $this->error('Invalid parameters', 400, $e->getMessage());

        } catch (\Throwable $e) {
            $this->logger->error('Error listing students', [
                'error' => $e->getMessage(),
                'params' => $request->query->all()
            ]);

            return $this->error('An error occurred while listing students', 500);
        }
    }

    #[Route('/{id}', name: 'api_v2_students_update', methods: ['PUT', 'PATCH'], requirements: ['id' => '\d+'])]
    #[OA\Put(
        summary: 'Actualizar un estudiante',
        description: 'Actualiza la información de un estudiante existente',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'firstName', type: 'string'),
                    new OA\Property(property: 'lastName', type: 'string'),
                    new OA\Property(property: 'phone', type: 'string', nullable: true),
                    new OA\Property(property: 'birthDate', type: 'string', format: 'date', nullable: true),
                    new OA\Property(property: 'gender', type: 'string', enum: ['M', 'F', 'O'], nullable: true),
                    new OA\Property(property: 'nationality', type: 'string', nullable: true),
                    new OA\Property(property: 'address', type: 'string', nullable: true),
                    new OA\Property(property: 'emergencyContact', type: 'string', nullable: true),
                ]
            )
        ),
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID del estudiante',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Estudiante actualizado exitosamente'),
            new OA\Response(response: 404, description: 'Estudiante no encontrado'),
            new OA\Response(response: 400, description: 'Datos inválidos'),
        ]
    )]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $data = $this->decodeJsonRequest($request);
            $dto = $this->createUpdateStudentDTOFromArray($id, $data);

            $errors = $this->validator->validate($dto);
            if (count($errors) > 0) {
                return $this->validationError($this->formatValidationErrors($errors));
            }

            $student = $this->updateStudentHandler->handle($dto);

            $this->logger->info('Student updated successfully', [
                'student_id' => $id
            ]);

            return $this->success($student, 200, [], ['student:read']);

        } catch (StudentNotFoundException $e) {
            return $this->notFound('Student');

        } catch (\InvalidArgumentException | \JsonException $e) {
            return $this->error('Invalid input', 400, $e->getMessage());

        } catch (\Throwable $e) {
            $this->logger->error('Error updating student', [
                'student_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->error('An error occurred while updating the student', 500);
        }
    }

    #[Route('/{id}', name: 'api_v2_students_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[OA\Delete(
        summary: 'Eliminar un estudiante',
        description: 'Elimina un estudiante del sistema',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID del estudiante',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 204, description: 'Estudiante eliminado exitosamente'),
            new OA\Response(response: 404, description: 'Estudiante no encontrado'),
        ]
    )]
    public function delete(int $id): JsonResponse
    {
        try {
            $this->deleteStudentHandler->handle($id);

            $this->logger->info('Student deleted successfully', [
                'student_id' => $id
            ]);

            return $this->json(null, 204);

        } catch (StudentNotFoundException $e) {
            return $this->notFound('Student');

        } catch (\Throwable $e) {
            $this->logger->error('Error deleting student', [
                'student_id' => $id,
                'error' => $e->getMessage()
            ]);

            return $this->error('An error occurred while deleting the student', 500);
        }
    }

    private function decodeJsonRequest(Request $request): array
    {
        $content = $request->getContent();
        
        if (empty($content)) {
            throw new \InvalidArgumentException('Request body cannot be empty');
        }

        try {
            return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \JsonException('Invalid JSON format: ' . $e->getMessage());
        }
    }

    private function createStudentDTOFromArray(array $data): CreateStudentDTO
    {
        return new CreateStudentDTO(
            email: trim($data['email'] ?? ''),
            firstName: trim($data['firstName'] ?? ''),
            lastName: trim($data['lastName'] ?? ''),
            phone: isset($data['phone']) ? trim($data['phone']) : null,
            birthDate: $data['birthDate'] ?? null,
            gender: $data['gender'] ?? null,
            nationality: isset($data['nationality']) ? trim($data['nationality']) : null,
            address: isset($data['address']) ? trim($data['address']) : null,
            emergencyContact: isset($data['emergencyContact']) ? trim($data['emergencyContact']) : null,
        );
    }

    private function createUpdateStudentDTOFromArray(int $id, array $data): UpdateStudentDTO
    {
        return new UpdateStudentDTO(
            id: $id,
            email: isset($data['email']) ? trim($data['email']) : null,
            firstName: isset($data['firstName']) ? trim($data['firstName']) : null,
            lastName: isset($data['lastName']) ? trim($data['lastName']) : null,
            phone: isset($data['phone']) ? trim($data['phone']) : null,
            birthDate: $data['birthDate'] ?? null,
            gender: $data['gender'] ?? null,
            nationality: isset($data['nationality']) ? trim($data['nationality']) : null,
            address: isset($data['address']) ? trim($data['address']) : null,
            emergencyContact: isset($data['emergencyContact']) ? trim($data['emergencyContact']) : null,
        );
    }

    private function formatValidationErrors($errors): array
    {
        $formatted = [];
        
        foreach ($errors as $error) {
            $formatted[$error->getPropertyPath()] = $error->getMessage();
        }
        
        return $formatted;
    }

    private function validateListParameters(
        int $page,
        int $perPage,
        string $sortBy,
        string $sortOrder
    ): array {
        $errors = [];

        if ($page < 1) {
            $errors['page'] = 'Page must be greater than 0';
        }

        if ($perPage < self::MIN_PER_PAGE) {
            $errors['perPage'] = sprintf('perPage must be at least %d', self::MIN_PER_PAGE);
        }

        if ($perPage > self::MAX_PER_PAGE) {
            $errors['perPage'] = sprintf('perPage cannot exceed %d', self::MAX_PER_PAGE);
        }

        $validSortFields = ['id', 'firstName', 'lastName', 'email', 'createdAt'];
        if (!in_array($sortBy, $validSortFields, true)) {
            $errors['sortBy'] = sprintf(
                'Invalid sort field. Allowed values: %s',
                implode(', ', $validSortFields)
            );
        }

        if (!in_array(strtolower($sortOrder), ['asc', 'desc'], true)) {
            $errors['sortOrder'] = 'Sort order must be "asc" or "desc"';
        }

        return $errors;
    }
}