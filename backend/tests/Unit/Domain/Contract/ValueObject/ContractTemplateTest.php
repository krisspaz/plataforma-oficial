<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Contract\ValueObject;

use App\Domain\Contract\ValueObject\ContractTemplate;
use PHPUnit\Framework\TestCase;

class ContractTemplateTest extends TestCase
{
    public function testCreateWithValidTemplate(): void
    {
        $template = new ContractTemplate(
            'Contrato Educativo',
            'Este contrato establece los términos...',
            ['enrollmentYear', 'studentName', 'guardianName', 'totalAmount']
        );

        $this->assertEquals('Contrato Educativo', $template->getTitle());
        $this->assertStringContainsString('términos', $template->getContent());
        $this->assertCount(4, $template->getPlaceholders());
    }

    public function testThrowsExceptionForEmptyTitle(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Title cannot be empty');

        new ContractTemplate('', 'Content', []);
    }

    public function testThrowsExceptionForEmptyContent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Content cannot be empty');

        new ContractTemplate('Title', '', []);
    }

    public function testRenderReplacesPlaceholders(): void
    {
        $template = new ContractTemplate(
            'Test',
            'Student: {{studentName}}, Year: {{enrollmentYear}}',
            ['studentName', 'enrollmentYear']
        );

        $rendered = $template->render([
            'studentName' => 'Juan Pérez',
            'enrollmentYear' => '2024'
        ]);

        $this->assertEquals('Student: Juan Pérez, Year: 2024', $rendered);
    }

    public function testRenderThrowsExceptionForMissingPlaceholder(): void
    {
        $template = new ContractTemplate(
            'Test',
            'Student: {{studentName}}',
            ['studentName']
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required placeholder');

        $template->render([]);
    }

    public function testValidateReturnsErrors(): void
    {
        $template = new ContractTemplate(
            'Test',
            'Student: {{studentName}}, Amount: {{amount}}',
            ['studentName', 'amount']
        );

        $errors = $template->validate(['studentName' => 'Test']);

        $this->assertArrayHasKey('amount', $errors);
    }
}
