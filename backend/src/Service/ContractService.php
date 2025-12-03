<?php

namespace App\Service;

use App\Entity\Contract;
use App\Entity\Enrollment;
use App\Entity\ParentEntity;
use Doctrine\ORM\EntityManagerInterface;

class ContractService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private string $projectDir
    ) {
    }

    public function generateContract(
        Enrollment $enrollment,
        ParentEntity $parent,
        float $totalAmount,
        ?int $installments = null
    ): Contract {
        $contract = new Contract();
        $contract->setEnrollment($enrollment);
        $contract->setParent($parent);
        $contract->setTotalAmount((string) $totalAmount);
        $contract->setInstallments($installments);
        $contract->setStatus('pending');

        // Generate PDF
        $pdfPath = $this->generatePDF($contract);
        $contract->setGeneratedPdf($pdfPath);

        $this->entityManager->persist($contract);
        $this->entityManager->flush();

        return $contract;
    }

    private function generatePDF(Contract $contract): string
    {
        // TODO: Implement PDF generation with TCPDF or Dompdf
        // For now, return a placeholder path
        
        $enrollment = $contract->getEnrollment();
        $student = $enrollment->getStudent();
        $parent = $contract->getParent();
        
        $pdfContent = $this->getPDFTemplate([
            'contractNumber' => $contract->getContractNumber(),
            'studentName' => $student->getUser()->getFirstName() . ' ' . $student->getUser()->getLastName(),
            'parentName' => $parent->getUser()->getFirstName() . ' ' . $parent->getUser()->getLastName(),
            'gradeName' => $enrollment->getGrade()->getName(),
            'sectionName' => $enrollment->getSection()->getName(),
            'totalAmount' => $contract->getTotalAmount(),
            'installments' => $contract->getInstallments(),
            'academicYear' => $enrollment->getAcademicYear(),
            'date' => (new \DateTime())->format('d/m/Y')
        ]);

        // Save PDF
        $filename = $contract->getContractNumber() . '.pdf';
        $directory = $this->projectDir . '/public/storage/contracts/';
        
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $filepath = $directory . $filename;
        file_put_contents($filepath, $pdfContent);

        return '/storage/contracts/' . $filename;
    }

    private function getPDFTemplate(array $data): string
    {
        // TODO: Implement proper PDF template with TCPDF
        // For now, return a simple HTML template
        
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Contrato {$data['contractNumber']}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; }
                h1 { text-align: center; color: #003366; }
                .header { text-align: center; margin-bottom: 30px; }
                .content { line-height: 1.6; }
                .signature { margin-top: 100px; }
                .signature-line { border-top: 1px solid #000; width: 300px; margin: 0 auto; padding-top: 10px; text-align: center; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>COLEGIO OXFORD BILINGUAL SCHOOL</h1>
                <p>Contrato de Servicios Educativos</p>
                <p><strong>No. {$data['contractNumber']}</strong></p>
            </div>
            
            <div class="content">
                <p><strong>Fecha:</strong> {$data['date']}</p>
                
                <h3>DATOS DEL ESTUDIANTE</h3>
                <p><strong>Nombre:</strong> {$data['studentName']}</p>
                <p><strong>Grado:</strong> {$data['gradeName']} - Sección {$data['sectionName']}</p>
                <p><strong>Año Académico:</strong> {$data['academicYear']}</p>
                
                <h3>DATOS DEL PADRE/TUTOR</h3>
                <p><strong>Nombre:</strong> {$data['parentName']}</p>
                
                <h3>CONDICIONES FINANCIERAS</h3>
                <p><strong>Monto Total:</strong> Q{$data['totalAmount']}</p>
                <p><strong>Cuotas:</strong> {$data['installments']} cuotas mensuales</p>
                
                <h3>TÉRMINOS Y CONDICIONES</h3>
                <p>El padre/tutor se compromete a:</p>
                <ul>
                    <li>Realizar los pagos en las fechas establecidas</li>
                    <li>Cumplir con el reglamento interno del colegio</li>
                    <li>Apoyar el proceso educativo del estudiante</li>
                    <li>Asistir a las reuniones convocadas por el colegio</li>
                </ul>
                
                <p>El colegio se compromete a:</p>
                <ul>
                    <li>Brindar educación de calidad</li>
                    <li>Mantener instalaciones adecuadas</li>
                    <li>Comunicar el progreso académico del estudiante</li>
                    <li>Garantizar un ambiente seguro de aprendizaje</li>
                </ul>
            </div>
            
            <div class="signature">
                <div class="signature-line">
                    <p>Firma del Padre/Tutor</p>
                    <p>{$data['parentName']}</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }

    public function signContract(Contract $contract, array $signatureData): Contract
    {
        // TODO: Implement digital signature
        // For now, just mark as signed
        
        $contract->markAsSigned([
            'signature_type' => 'digital',
            'signature_data' => $signatureData,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);

        // Generate signed PDF
        $signedPdfPath = $this->generateSignedPDF($contract);
        $contract->setSignedPdf($signedPdfPath);

        $this->entityManager->flush();

        return $contract;
    }

    private function generateSignedPDF(Contract $contract): string
    {
        // TODO: Add signature to PDF
        // For now, copy the generated PDF
        
        $originalPath = $this->projectDir . '/public' . $contract->getGeneratedPdf();
        $signedFilename = str_replace('.pdf', '_signed.pdf', basename($contract->getGeneratedPdf()));
        $signedPath = dirname($originalPath) . '/' . $signedFilename;

        if (file_exists($originalPath)) {
            copy($originalPath, $signedPath);
        }

        return '/storage/contracts/' . $signedFilename;
    }
}
