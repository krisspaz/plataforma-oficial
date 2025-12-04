<?php

declare(strict_types=1);

namespace App\Application\Contract\Command;

/**
 * Command to generate a contract.
 */
final class GenerateContractCommand
{
    public function __construct(
        public readonly int $enrollmentId,
        public readonly ?int $parentId = null,
        public readonly ?string $templateName = null,
        public readonly ?array $customData = null
    ) {}
}
