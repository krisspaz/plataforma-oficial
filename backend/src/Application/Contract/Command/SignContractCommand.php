<?php

declare(strict_types=1);

namespace App\Application\Contract\Command;

use App\Domain\Contract\ValueObject\SignatureData;

/**
 * Command to sign a contract.
 */
final class SignContractCommand
{
    public function __construct(
        public readonly int $contractId,
        public readonly string $signerName,
        public readonly string $signerEmail,
        public readonly ?string $signatureImageBase64 = null,
        public readonly ?string $ipAddress = null,
        public readonly ?array $metadata = null
    ) {}

    public function getSignatureData(): SignatureData
    {
        return SignatureData::create(
            $this->signerName,
            $this->signerEmail,
            $this->signatureImageBase64,
            $this->ipAddress
        );
    }
}
