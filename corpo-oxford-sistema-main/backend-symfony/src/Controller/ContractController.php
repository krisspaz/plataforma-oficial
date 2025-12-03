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
    ) {
    }

    #[Route('', name: 'api_contracts_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $status = $request->query->get('status');
        
        if ($status) {
            $contracts = $this->contractRepository->findByStatus($status);
        } else {
            $contracts = $this->contractRepository->findAll();
        }
        
        return $this->json($contracts, Response::HTTP_OK, [], [
            'groups' => ['contract:read']
        ]);
    }

    #[Route('/pending', name: 'api_contracts_pending', methods: ['GET'])]
    public function pending(): JsonResponse
    {
        $contracts = $this->contractRepository->findPending();
        
        return $this->json($contracts, Response::HTTP_OK, [], [
            'groups' => ['contract:read']
        ]);
    }

    #[Route('', name: 'api_contracts_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['enrollmentId']) || !isset($data['parentId']) || !isset($data['totalAmount'])) {
            return $this->json([
                'error' => 'Enrollment ID, Parent ID, and total amount are required'
            ], Response::HTTP_BAD_REQUEST);
        }

        $enrollment = $this->enrollmentRepository->find($data['enrollmentId']);
        if (!$enrollment) {
            return $this->json(['error' => 'Enrollment not found'], Response::HTTP_NOT_FOUND);
        }

        $parent = $this->parentRepository->find($data['parentId']);
        if (!$parent) {
            return $this->json(['error' => 'Parent not found'], Response::HTTP_NOT_FOUND);
        }

        $contract = $this->contractService->generateContract(
            $enrollment,
            $parent,
            (float) $data['totalAmount'],
            $data['installments'] ?? null
        );

        // Send notification
        $this->notificationService->createNotification(
            $parent->getUser(),
            'Nuevo Contrato Generado',
            "Se ha generado el contrato {$contract->getContractNumber()}. Por favor revíselo y fírmelo.",
            'contract',
            ['contractId' => $contract->getId(), 'contractNumber' => $contract->getContractNumber()]
        );

        return $this->json([
            'message' => 'Contract created successfully',
            'contract' => $contract
        ], Response::HTTP_CREATED, [], [
            'groups' => ['contract:read']
        ]);
    }

    #[Route('/{id}', name: 'api_contracts_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $contract = $this->contractRepository->find($id);
        
        if (!$contract) {
            return $this->json(['error' => 'Contract not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($contract, Response::HTTP_OK, [], [
            'groups' => ['contract:read']
        ]);
    }

    #[Route('/{id}/sign', name: 'api_contracts_sign', methods: ['POST'])]
    public function sign(int $id, Request $request): JsonResponse
    {
        $contract = $this->contractRepository->find($id);
        
        if (!$contract) {
            return $this->json(['error' => 'Contract not found'], Response::HTTP_NOT_FOUND);
        }

        if ($contract->isSigned()) {
            return $this->json(['error' => 'Contract is already signed'], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['signature'])) {
            return $this->json(['error' => 'Signature data required'], Response::HTTP_BAD_REQUEST);
        }

        $signedContract = $this->contractService->signContract($contract, $data['signature']);

        // Send notification
        $this->notificationService->notifyContractSigned(
            $signedContract->getParent()->getUser(),
            $signedContract->getContractNumber()
        );

        return $this->json([
            'message' => 'Contract signed successfully',
            'contract' => $signedContract
        ], Response::HTTP_OK, [], [
            'groups' => ['contract:read']
        ]);
    }

    #[Route('/enrollment/{enrollmentId}', name: 'api_contracts_by_enrollment', methods: ['GET'])]
    public function byEnrollment(int $enrollmentId): JsonResponse
    {
        $contracts = $this->contractRepository->findByEnrollment($enrollmentId);
        
        return $this->json($contracts, Response::HTTP_OK, [], [
            'groups' => ['contract:read']
        ]);
    }

    #[Route('/student/{studentId}', name: 'api_contracts_by_student', methods: ['GET'])]
    public function byStudent(int $studentId): JsonResponse
    {
        $contracts = $this->contractRepository->findByStudent($studentId);
        
        return $this->json($contracts, Response::HTTP_OK, [], [
            'groups' => ['contract:read']
        ]);
    }
}
