<?php

namespace App\Controller;

use App\Entity\Contract;
use App\Repository\ContractRepository;
use App\Repository\EnrollmentRepository;
use App\Repository\ParentRepository;
use App\Service\ContractService;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/contracts')]
class ContractController extends AbstractController
{
    public function __construct(
        private ContractRepository $contractRepository,
        private EnrollmentRepository $enrollmentRepository,
        private ParentRepository $parentRepository,
        private ContractService $contractService,
        private NotificationService $notificationService,
        private EntityManagerInterface $entityManager
    ) {}

    // ===== Helpers =====
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

    private function notifyParentContract(Contract $contract, string $message): void
    {
        $this->notificationService->createNotification(
            $contract->getParent()->getUser(),
            'Contrato',
            $message,
            'contract',
            ['contractId' => $contract->getId(), 'contractNumber' => $contract->getContractNumber()]
        );
    }

    // ===== Endpoints =====
    #[Route('', name: 'api_contracts_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $status = $request->query->get('status');
        $contracts = $status
            ? $this->contractRepository->findByStatus($status)
            : $this->contractRepository->findAll();

        return $this->respond($contracts);
    }

    #[Route('/pending', name: 'api_contracts_pending', methods: ['GET'])]
    public function pending(): JsonResponse
    {
        $contracts = $this->contractRepository->findPending();
        return $this->respond($contracts);
    }

    #[Route('', name: 'api_contracts_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['enrollmentId'] ?? null) || empty($data['parentId'] ?? null) || empty($data['totalAmount'] ?? null)) {
            return $this->respondBadRequest('Enrollment ID, Parent ID, and total amount are required');
        }

        $enrollment = $this->enrollmentRepository->find($data['enrollmentId']);
        if (!$enrollment) return $this->respondNotFound('Enrollment not found');

        $parent = $this->parentRepository->find($data['parentId']);
        if (!$parent) return $this->respondNotFound('Parent not found');

        $contract = $this->contractService->generateContract(
            $enrollment,
            $parent,
            (float) $data['totalAmount'],
            $data['installments'] ?? null
        );

        $this->notifyParentContract($contract, "Se ha generado el contrato {$contract->getContractNumber()}. Por favor revíselo y fírmelo.");

        return $this->respond([
            'message' => 'Contract created successfully',
            'contract' => $contract
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_contracts_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $contract = $this->contractRepository->find($id);
        if (!$contract) return $this->respondNotFound('Contract not found');

        return $this->respond($contract);
    }

    #[Route('/{id}/sign', name: 'api_contracts_sign', methods: ['POST'])]
    public function sign(int $id, Request $request): JsonResponse
    {
        $contract = $this->contractRepository->find($id);
        if (!$contract) return $this->respondNotFound('Contract not found');

        if ($contract->isSigned()) return $this->respondBadRequest('Contract is already signed');

        $data = json_decode($request->getContent(), true);
        if (empty($data['signature'] ?? null)) return $this->respondBadRequest('Signature data required');

        $signedContract = $this->contractService->signContract($contract, $data['signature']);
        $this->notificationService->notifyContractSigned(
            $signedContract->getParent()->getUser(),
            $signedContract->getContractNumber()
        );

        return $this->respond([
            'message' => 'Contract signed successfully',
            'contract' => $signedContract
        ]);
    }

    #[Route('/enrollment/{enrollmentId}', name: 'api_contracts_by_enrollment', methods: ['GET'])]
    public function byEnrollment(int $enrollmentId): JsonResponse
    {
        $contracts = $this->contractRepository->findByEnrollment($enrollmentId);
        return $this->respond($contracts);
    }

    #[Route('/student/{studentId}', name: 'api_contracts_by_student', methods: ['GET'])]
    public function byStudent(int $studentId): JsonResponse
    {
        $contracts = $this->contractRepository->findByStudent($studentId);
        return $this->respond($contracts);
    }
}
