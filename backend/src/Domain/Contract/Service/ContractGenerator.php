<?php

declare(strict_types=1);

namespace App\Domain\Contract\Service;

use App\Domain\Contract\ValueObject\ContractTemplate;
use App\Entity\Contract;
use App\Entity\Enrollment;
use DateTimeImmutable;

/**
 * Domain service for generating contract content.
 */
final class ContractGenerator
{
    /**
     * Generate contract data from enrollment.
     */
    public function generateContractData(Enrollment $enrollment, Contract $contract): array
    {
        $student = $enrollment->getStudent();
        $grade = $enrollment->getGrade();
        $section = $enrollment->getSection();

        $currentYear = (int) date('Y');
        $academicYear = $enrollment->getAcademicYear();

        return [
            // Contract info
            'contract_number' => $contract->getContractNumber(),
            'resolution_number' => $contract->getResolutionNumber() ?? 'N/A',
            'contract_date' => (new DateTimeImmutable())->format('d/m/Y'),

            // Student info
            'student_name' => sprintf('%s %s', $student->getFirstName(), $student->getLastName()),
            'student_first_name' => $student->getFirstName(),
            'student_last_name' => $student->getLastName(),
            'student_email' => $student->getEmail(),
            'student_birth_date' => $student->getBirthDate()?->format('d/m/Y') ?? 'N/A',

            // Academic info
            'grade' => $grade->getName(),
            'section' => $section->getName(),
            'academic_year' => $academicYear,
            'current_year' => $currentYear,

            // Financial info
            'total_amount' => number_format((float) $contract->getTotalAmount(), 2),
            'installments' => $contract->getInstallments() ?? 1,
            'installment_amount' => $contract->getInstallmentAmount()
                ? number_format($contract->getInstallmentAmount(), 2)
                : number_format((float) $contract->getTotalAmount(), 2),

            // Parent info (if available)
            'parent_name' => $contract->getParent()
                ? sprintf('%s %s', $contract->getParent()->getFirstName(), $contract->getParent()->getLastName())
                : 'N/A',
            'parent_email' => $contract->getParent()?->getEmail() ?? 'N/A',
            'parent_phone' => $contract->getParent()?->getPhone() ?? 'N/A',

            // School info (these would come from configuration)
            'school_name' => 'Colegio [Nombre]',
            'school_address' => 'Dirección del Colegio',
            'school_phone' => 'Teléfono del Colegio',
            'school_email' => 'info@colegio.edu.gt',

            // Legal info
            'legal_representative' => 'Representante Legal',
            'legal_representative_id' => 'DPI/NIT',
        ];
    }

    /**
     * Render contract from template.
     */
    public function renderContract(ContractTemplate $template, array $data): string
    {
        // Validate required variables
        $missing = $template->validateData($data);

        if (!empty($missing)) {
            throw new \DomainException(
                sprintf('Missing required variables: %s', implode(', ', $missing))
            );
        }

        return $template->render($data);
    }

    /**
     * Get default contract template.
     */
    public function getDefaultTemplate(): ContractTemplate
    {
        $content = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Contrato de Servicios Educativos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 30px; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 10px; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; margin-bottom: 10px; }
        .signature-area { margin-top: 60px; }
        .signature-line { border-top: 1px solid #000; width: 300px; margin: 40px auto 5px; }
        .footer { margin-top: 40px; font-size: 12px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">CONTRATO DE SERVICIOS EDUCATIVOS</div>
        <div>Año Académico {{academic_year}}</div>
        <div>Contrato No. {{contract_number}}</div>
        <div>Resolución No. {{resolution_number}}</div>
    </div>

    <div class="section">
        <div class="section-title">I. PARTES CONTRATANTES</div>
        <p>
            Por una parte <strong>{{school_name}}</strong>, representado legalmente por 
            <strong>{{legal_representative}}</strong>, con DPI/NIT <strong>{{legal_representative_id}}</strong>,
            en adelante denominado "EL COLEGIO", y por la otra parte:
        </p>
        <p>
            <strong>{{parent_name}}</strong>, en su calidad de padre/madre o encargado(a) legal del estudiante
            <strong>{{student_name}}</strong>, en adelante denominado "EL CONTRATANTE".
        </p>
    </div>

    <div class="section">
        <div class="section-title">II. DATOS DEL ESTUDIANTE</div>
        <p>
            Nombre: <strong>{{student_name}}</strong><br>
            Fecha de Nacimiento: <strong>{{student_birth_date}}</strong><br>
            Grado: <strong>{{grade}} - Sección {{section}}</strong><br>
            Año Académico: <strong>{{academic_year}}</strong>
        </p>
    </div>

    <div class="section">
        <div class="section-title">III. OBJETO DEL CONTRATO</div>
        <p>
            EL COLEGIO se compromete a prestar servicios educativos al estudiante mencionado,
            conforme al plan de estudios aprobado por el Ministerio de Educación de Guatemala,
            durante el año académico {{academic_year}}.
        </p>
    </div>

    <div class="section">
        <div class="section-title">IV. OBLIGACIONES ECONÓMICAS</div>
        <p>
            Monto Total: <strong>Q{{total_amount}}</strong><br>
            Forma de Pago: <strong>{{installments}} cuotas</strong><br>
            Monto por Cuota: <strong>Q{{installment_amount}}</strong>
        </p>
        <p>
            EL CONTRATANTE se compromete a realizar los pagos en las fechas establecidas
            por EL COLEGIO. El incumplimiento en los pagos podrá resultar en la suspensión
            de servicios educativos.
        </p>
    </div>

    <div class="section">
        <div class="section-title">V. OBLIGACIONES DEL CONTRATANTE</div>
        <ul>
            <li>Cumplir con los pagos en las fechas establecidas</li>
            <li>Respetar el reglamento interno del colegio</li>
            <li>Asistir a reuniones y citaciones del colegio</li>
            <li>Velar por la asistencia y puntualidad del estudiante</li>
        </ul>
    </div>

    <div class="section">
        <div class="section-title">VI. OBLIGACIONES DEL COLEGIO</div>
        <ul>
            <li>Brindar educación de calidad conforme al plan de estudios</li>
            <li>Proporcionar instalaciones adecuadas</li>
            <li>Mantener comunicación con los padres de familia</li>
            <li>Emitir certificaciones y documentos académicos</li>
        </ul>
    </div>

    <div class="section">
        <div class="section-title">VII. VIGENCIA</div>
        <p>
            El presente contrato tendrá vigencia durante el año académico {{academic_year}},
            iniciando en enero y finalizando en octubre del año {{current_year}}.
        </p>
    </div>

    <div class="signature-area">
        <p>Fecha: {{contract_date}}</p>
        
        <div style="display: flex; justify-content: space-around; margin-top: 60px;">
            <div style="text-align: center;">
                <div class="signature-line"></div>
                <div>{{legal_representative}}</div>
                <div>Representante Legal</div>
                <div>{{school_name}}</div>
            </div>
            
            <div style="text-align: center;">
                <div class="signature-line"></div>
                <div>{{parent_name}}</div>
                <div>Padre/Madre o Encargado</div>
                <div>DPI: _______________</div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>{{school_name}} - {{school_address}}</p>
        <p>Tel: {{school_phone}} - Email: {{school_email}}</p>
    </div>
</body>
</html>
HTML;

        $variables = ContractTemplate::extractVariables($content);

        return new ContractTemplate('Contrato Estándar', $content, $variables);
    }
}
