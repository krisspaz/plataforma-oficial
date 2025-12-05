<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Parent\Query\GetMyChildrenQuery;
use App\Application\Parent\Query\GetMyContractsQuery;
use App\Application\Parent\Query\GetMyPaymentsQuery;
use App\Application\Parent\Query\GetParentByIdQuery;
use App\Application\Parent\Query\GetParentsByStudentQuery;
use App\Application\Parent\Query\GetParentsQuery;
use App\Application\Parent\Query\SearchParentsQuery;
use App\Controller\Traits\ApiResponseTrait;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

#[Route('/api/parents')]
#[OA\Tag(name: 'Parents')]
class ParentController extends AbstractController
{
    use ApiResponseTrait;

    public function __construct(
        private readonly MessageBusInterface $queryBus
    ) {}

    #[Route('', name: 'api_parents_index', methods: ['GET'])]
    #[OA\Get(path: '/api/parents', summary: 'List all parents')]
    public function index(): JsonResponse
    {
        $parents = $this->handleQuery(new GetParentsQuery());

        return $this->success($parents, 200, [], ['parent:read']);
    }

    #[Route('/{id}', name: 'api_parents_show', methods: ['GET'])]
    #[OA\Get(path: '/api/parents/{id}', summary: 'Get parent details')]
    public function show(int $id): JsonResponse
    {
        $parent = $this->handleQuery(new GetParentByIdQuery($id));

        if (!$parent) {
            return $this->notFound('Parent');
        }

        return $this->success($parent, 200, [], ['parent:read']);
    }

    #[Route('/my-children', name: 'api_parents_my_children', methods: ['GET'])]
    #[OA\Get(path: '/api/parents/my-children', summary: 'Get authenticated parent children')]
    public function myChildren(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->unauthorized();
        }

        $result = $this->handleQuery(new GetMyChildrenQuery($user));
        if (!$result) {
            return $this->notFound('Parent profile');
        }

        return $this->success($result, 200, [], ['student:read']);
    }

    #[Route('/student/{studentId}', name: 'api_parents_by_student', methods: ['GET'])]
    #[OA\Get(path: '/api/parents/student/{studentId}', summary: 'Get parents by student ID')]
    public function byStudent(int $studentId): JsonResponse
    {
        $parents = $this->handleQuery(new GetParentsByStudentQuery($studentId));

        return $this->success($parents, 200, [], ['parent:read']);
    }

    #[Route('/search', name: 'api_parents_search', methods: ['GET'])]
    #[OA\Get(path: '/api/parents/search', summary: 'Search parents')]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');

        if (empty($query)) {
            return $this->validationError(['q' => 'Search query required']);
        }

        $parents = $this->handleQuery(new SearchParentsQuery($query));

        return $this->success($parents, 200, [], ['parent:read']);
    }

    #[Route('/my-payments', name: 'api_parents_my_payments', methods: ['GET'])]
    #[OA\Get(path: '/api/parents/my-payments', summary: 'Get authenticated parent payments')]
    public function myPayments(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->unauthorized();
        }

        $result = $this->handleQuery(new GetMyPaymentsQuery($user));
        if (!$result) {
            return $this->notFound('Parent profile');
        }

        return $this->success($result, 200, [], ['payment:read']);
    }

    #[Route('/my-contracts', name: 'api_parents_my_contracts', methods: ['GET'])]
    #[OA\Get(path: '/api/parents/my-contracts', summary: 'Get authenticated parent contracts')]
    public function myContracts(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->unauthorized();
        }

        $result = $this->handleQuery(new GetMyContractsQuery($user));
        if (!$result) {
            return $this->notFound('Parent profile');
        }

        return $this->success($result, 200, [], ['contract:read']);
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
