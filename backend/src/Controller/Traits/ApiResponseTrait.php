<?php

declare(strict_types=1);

namespace App\Controller\Traits;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Trait para respuestas API consistentes
 * Proporciona métodos helper para respuestas JSON estandarizadas
 */
trait ApiResponseTrait
{
    /**
     * Respuesta exitosa con datos
     */
    protected function success(
        mixed $data = null,
        int $status = Response::HTTP_OK,
        array $headers = [],
        array $groups = []
    ): JsonResponse {
        $context = $groups ? ['groups' => $groups] : [];
        
        return $this->json($data, $status, $headers, $context);
    }

    /**
     * Respuesta de error con mensaje
     */
    protected function error(
        string $message,
        int $status = Response::HTTP_BAD_REQUEST,
        array $additionalData = []
    ): JsonResponse {
        $data = array_merge(['error' => $message], $additionalData);
        
        return $this->json($data, $status);
    }

    /**
     * Respuesta de validación fallida
     */
    protected function validationError(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return $this->json([
            'error' => $message,
            'validation_errors' => $errors
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Respuesta de recurso no encontrado
     */
    protected function notFound(string $resource = 'Resource'): JsonResponse
    {
        return $this->error("{$resource} not found", Response::HTTP_NOT_FOUND);
    }

    /**
     * Respuesta de no autorizado
     */
    protected function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->error($message, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Respuesta de prohibido
     */
    protected function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->error($message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Respuesta de creación exitosa
     */
    protected function created(mixed $data = null, array $groups = []): JsonResponse
    {
        return $this->success($data, Response::HTTP_CREATED, [], $groups);
    }

    /**
     * Respuesta sin contenido (para DELETE exitoso)
     */
    protected function noContent(): JsonResponse
    {
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Respuesta paginada
     */
    protected function paginated(
        array $items,
        int $total,
        int $page,
        int $perPage,
        array $groups = []
    ): JsonResponse {
        $data = [
            'data' => $items,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => (int) ceil($total / $perPage),
                'hasMore' => ($page * $perPage) < $total
            ]
        ];

        $context = $groups ? ['groups' => $groups] : [];
        
        return $this->json($data, Response::HTTP_OK, [], $context);
    }
}
