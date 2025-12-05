<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Contract\Command\CreateContractCommand;
use App\Application\Contract\Command\SignContractCommand;
use App\Application\Contract\Query\GetContractByIdQuery;
use App\Application\Contract\Query\GetContractsByEnrollmentQuery;
use App\Application\Contract\Query\GetContractsByStudentQuery;
use App\Application\Contract\Query\GetContractsQuery;
use App\Application\Contract\Query\GetPendingContractsQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/contracts')]
class ContractController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $queryBus,
        private readonly MessageBusInterface $commandBus
    ) {}

    private function respond(mixed $data, int $status = 200): JsonResponse
    {
        return $this->json($data, $status);
    }

    private function respondNotFound(string $message = 'Not found'): JsonResponse
    {
        return $this->respond(['error' => $message], Response::HTTP_NOT_FOUND);
    }

    private function respondBadRequest(string $message): JsonResponse
    {
        return $this->respond(['error' => $message], Response::HTTP_BAD_REQUEST);
    }

    #[Route('', name: 'api_contracts_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $status = $request->query->get('status');
        $contracts = $this->handleQuery(new GetContractsQuery($status));

        return $this->respond($contracts);
    }

    #[Route('/pending', name: 'api_contracts_pending', methods: ['GET'])]
    public function pending(): JsonResponse
    {
        $contracts = $this->handleQuery(new GetPendingContractsQuery());
        return $this->respond($contracts);
    }

    #[Route('', name: 'api_contracts_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['enrollmentId'] ?? null) || empty($data['parentId'] ?? null) || empty($data['totalAmount'] ?? null)) {
            return $this->respondBadRequest('Enrollment ID, Parent ID, and total amount are required');
        }

        $result = $this->handleCommand(new CreateContractCommand(
            enrollmentId: (int) $data['enrollmentId'],
            parentId: (int) $data['parentId'],
            totalAmount: (float) $data['totalAmount'],
            installments: $data['installments'] ?? null
        ));

        if (isset($result['error'])) {
            return $this->respond(['error' => $result['error']], $result['code']);
        }

        return $this->respond([
            'message' => 'Contract created successfully',
            'contract' => $result['contract']
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_contracts_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $contract = $this->handleQuery(new GetContractByIdQuery($id));
        if (!$contract) return $this->respondNotFound('Contract not found');

        return $this->respond($contract);
    }

    #[Route('/{id}/sign', name: 'api_contracts_sign', methods: ['POST'])]
    public function sign(int $id, Request $request): JsonResponse
    {
        $contract = $this->handleQuery(new GetContractByIdQuery($id));
        if (!$contract) return $this->respondNotFound('Contract not found');

        if ($contract->isSigned()) {
            return $this->respondBadRequest('Contract is already signed');
        }

        $data = json_decode($request->getContent(), true);
        if (empty($data['signature'] ?? null)) {
            return $this->respondBadRequest('Signature data required');
        }

        $result = $this->handleCommand(new SignContractCommand(
            contractId: $id,
            signature: $data['signature']
        ));

        return $this->respond([
            'message' => 'Contract signed successfully',
            'contract' => $result['contract'] ?? $result
        ]);
    }

    #[Route('/enrollment/{enrollmentId}', name: 'api_contracts_by_enrollment', methods: ['GET'])]
    public function byEnrollment(int $enrollmentId): JsonResponse
    {
        $contracts = $this->handleQuery(new GetContractsByEnrollmentQuery($enrollmentId));
        return $this->respond($contracts);
    }

    #[Route('/student/{studentId}', name: 'api_contracts_by_student', methods: ['GET'])]
    public function byStudent(int $studentId): JsonResponse
    {
        $contracts = $this->handleQuery(new GetContractsByStudentQuery($studentId));
        return $this->respond($contracts);
    }

    private function handleQuery(object $query): mixed
    {
        $envelope = $this->queryBus->dispatch($query);
        $handledStamp = $envelope->last(HandledStamp::class);
        return $handledStamp?->getResult();
    }

    private function handleCommand(object $command): mixed
    {
        $envelope = $this->commandBus->dispatch($command);
        $handledStamp = $envelope->last(HandledStamp::class);
        return $handledStamp?->getResult();
    }
}
