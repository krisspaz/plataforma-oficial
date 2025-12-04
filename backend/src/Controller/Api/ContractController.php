<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Application\Contract\Command\GenerateContractCommand;
use App\Application\Contract\Command\GenerateContractHandler;
use App\Application\Contract\Command\SignContractCommand;
use App\Application\Contract\Command\SignContractHandler;
use App\Repository\ContractRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/contracts')]
class ContractController extends AbstractController
{
    public function __construct(
        private readonly GenerateContractHandler $generateContractHandler,
        private readonly SignContractHandler $signContractHandler,
        private readonly ContractRepository $contractRepository,
        private readonly string $contractsDirectory
    ) {}

    /**
     * Generate a new contract.
     */
    #[Route('/generate', name: 'api_contracts_generate', methods: ['POST'])]
    #[IsGranted('ROLE_SECRETARIA')]
    public function generate(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $command = new GenerateContractCommand(
                enrollmentId: $data['enrollment_id'],
                parentId: $data['parent_id'] ?? null,
                templateName: $data['template_name'] ?? null,
                customData: $data['custom_data'] ?? null
            );

            $contract = ($this->generateContractHandler)($command);

            return $this->json([
                'success' => true,
                'message' => 'Contrato generado exitosamente',
                'data' => [
                    'contract_id' => $contract->getId(),
                    'contract_number' => $contract->getContractNumber(),
                    'pdf_filename' => $contract->getGeneratedPdf(),
                    'status' => $contract->getStatus(),
                ]
            ], Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'error' => 'Error generando contrato'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Sign a contract.
     */
    #[Route('/{id}/sign', name: 'api_contracts_sign', methods: ['POST'])]
    #[IsGranted('ROLE_PARENT')]
    public function sign(int $id, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $command = new SignContractCommand(
                contractId: $id,
                signerName: $data['signer_name'],
                signerEmail: $data['signer_email'],
                signatureImageBase64: $data['signature_image'] ?? null,
                ipAddress: $request->getClientIp(),
                metadata: $data['metadata'] ?? null
            );

            ($this->signContractHandler)($command);

            return $this->json([
                'success' => true,
                'message' => 'Contrato firmado exitosamente'
            ]);
        } catch (\InvalidArgumentException | \DomainException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Download contract PDF.
     */
    #[Route('/{id}/download', name: 'api_contracts_download', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function download(int $id): Response
    {
        $contract = $this->contractRepository->find($id);

        if (!$contract) {
            return $this->json([
                'success' => false,
                'error' => 'Contrato no encontrado'
            ], Response::HTTP_NOT_FOUND);
        }

        $filename = $contract->getGeneratedPdf() ?? $contract->getSignedPdf();

        if (!$filename) {
            return $this->json([
                'success' => false,
                'error' => 'No hay PDF disponible'
            ], Response::HTTP_NOT_FOUND);
        }

        $filepath = $this->contractsDirectory . '/' . $filename;

        if (!file_exists($filepath)) {
            return $this->json([
                'success' => false,
                'error' => 'Archivo no encontrado'
            ], Response::HTTP_NOT_FOUND);
        }

        $response = new BinaryFileResponse($filepath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );

        return $response;
    }

    /**
     * Get contract details.
     */
    #[Route('/{id}', name: 'api_contracts_get', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function get(int $id): JsonResponse
    {
        $contract = $this->contractRepository->find($id);

        if (!$contract) {
            return $this->json([
                'success' => false,
                'error' => 'Contrato no encontrado'
            ], Response::HTTP_NOT_FOUND);
        }

        $enrollment = $contract->getEnrollment();
        $student = $enrollment->getStudent();

        return $this->json([
            'success' => true,
            'data' => [
                'id' => $contract->getId(),
                'contract_number' => $contract->getContractNumber(),
                'resolution_number' => $contract->getResolutionNumber(),
                'status' => $contract->getStatus(),
                'total_amount' => $contract->getTotalAmount(),
                'installments' => $contract->getInstallments(),
                'student_name' => sprintf('%s %s', $student->getFirstName(), $student->getLastName()),
                'grade' => $enrollment->getGrade()->getName(),
                'has_generated_pdf' => $contract->getGeneratedPdf() !== null,
                'has_signed_pdf' => $contract->getSignedPdf() !== null,
                'is_signed' => $contract->isSigned(),
                'signature_metadata' => $contract->getSignatureMetadata(),
                'created_at' => $contract->getCreatedAt()->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    /**
     * List contracts for an enrollment.
     */
    #[Route('/enrollment/{enrollmentId}', name: 'api_contracts_by_enrollment', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function listByEnrollment(int $enrollmentId): JsonResponse
    {
        $contracts = $this->contractRepository->findBy(['enrollment' => $enrollmentId]);

        $data = array_map(function ($contract) {
            return [
                'id' => $contract->getId(),
                'contract_number' => $contract->getContractNumber(),
                'status' => $contract->getStatus(),
                'total_amount' => $contract->getTotalAmount(),
                'is_signed' => $contract->isSigned(),
                'created_at' => $contract->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $contracts);

        return $this->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
