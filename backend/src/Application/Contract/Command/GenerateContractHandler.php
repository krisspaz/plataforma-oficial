<?php

declare(strict_types=1);

namespace App\Application\Contract\Command;

use App\Domain\Contract\Service\ContractGenerator;
use App\Domain\Contract\Service\PDFGenerator;
use App\Entity\Contract;
use App\Repository\ContractRepository;
use App\Repository\EnrollmentRepository;
use App\Repository\ParentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GenerateContractHandler
{
    public function __construct(
        private readonly EnrollmentRepository $enrollmentRepository,
        private readonly ParentRepository $parentRepository,
        private readonly ContractRepository $contractRepository,
        private readonly ContractGenerator $contractGenerator,
        private readonly PDFGenerator $pdfGenerator,
        private readonly EntityManagerInterface $entityManager,
        private readonly string $contractsDirectory
    ) {}

    public function __invoke(GenerateContractCommand $command): Contract
    {
        // Get enrollment
        $enrollment = $this->enrollmentRepository->find($command->enrollmentId);

        if (!$enrollment) {
            throw new \InvalidArgumentException(
                sprintf('Enrollment with ID %d not found', $command->enrollmentId)
            );
        }

        // Get or create contract
        $contract = $this->contractRepository->findOneBy(['enrollment' => $enrollment]);

        if (!$contract) {
            $contract = new Contract();
            $contract->setEnrollment($enrollment);
            $contract->setStatus('pending');
        }

        // Set parent if provided
        if ($command->parentId) {
            $parent = $this->parentRepository->find($command->parentId);
            if ($parent) {
                $contract->setParent($parent);
            }
        }

        // Set financial data from enrollment or custom data
        if ($command->customData) {
            $contract->setTotalAmount((string) ($command->customData['total_amount'] ?? 0));
            $contract->setInstallments($command->customData['installments'] ?? 1);
        }

        // Generate contract data
        $contractData = $this->contractGenerator->generateContractData($enrollment, $contract);

        // Merge custom data if provided
        if ($command->customData) {
            $contractData = array_merge($contractData, $command->customData);
        }

        // Get template
        $template = $this->contractGenerator->getDefaultTemplate();

        // Render contract HTML
        $html = $this->contractGenerator->renderContract($template, $contractData);

        // Generate PDF
        $filename = sprintf(
            'contract_%s_%s.pdf',
            $contract->getContractNumber(),
            date('Ymd')
        );

        $filepath = $this->contractsDirectory . '/' . $filename;

        // Ensure directory exists
        if (!is_dir($this->contractsDirectory)) {
            mkdir($this->contractsDirectory, 0755, true);
        }

        // Set PDF metadata
        $this->pdfGenerator->setMetadata(
            'Contrato de Servicios Educativos',
            'Sistema Escolar',
            sprintf('Contrato para %s', $contractData['student_name'])
        );

        // Generate and save PDF
        $this->pdfGenerator->generateAndSave($html, $filepath);

        // Update contract
        $contract->setGeneratedPdf($filename);

        $this->entityManager->persist($contract);
        $this->entityManager->flush();

        return $contract;
    }
}
