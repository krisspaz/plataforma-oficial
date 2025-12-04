<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Contract;

use App\Domain\Contract\ValueObject\ContractTemplate;
use PHPUnit\Framework\TestCase;

class ContractTemplateTest extends TestCase
{
    public function testCreateTemplate(): void
    {
        $template = new ContractTemplate('enrollment');

        $this->assertSame('enrollment', $template->getType());
        $this->assertNotEmpty($template->getTemplate());
    }

    public function testRenderWithVariables(): void
    {
        $template = new ContractTemplate('enrollment');

        $html = $template->render([
            'student_name' => 'Juan Pérez',
            'total_amount' => 'Q1,500.00',
            'num_installments' => 10,
            'date' => '2025-01-01'
        ]);

        $this->assertStringContainsString('Juan Pérez', $html);
        $this->assertStringContainsString('Q1,500.00', $html);
    }

    public function testInvalidTemplateType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ContractTemplate('invalid_type');
    }
}
