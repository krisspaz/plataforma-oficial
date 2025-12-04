<?php

declare(strict_types=1);

namespace App\Domain\Contract\ValueObject;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Value Object representing signature data.
 */
final class SignatureData
{
    private readonly string $signerName;
    private readonly string $signerEmail;
    private readonly ?string $signatureImage;
    private readonly DateTimeImmutable $signedAt;
    private readonly string $ipAddress;
    private readonly array $metadata;

    public function __construct(
        string $signerName,
        string $signerEmail,
        ?string $signatureImage,
        DateTimeImmutable $signedAt,
        string $ipAddress,
        array $metadata = []
    ) {
        if (empty(trim($signerName))) {
            throw new InvalidArgumentException('Signer name cannot be empty');
        }

        if (!filter_var($signerEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email address');
        }

        if (!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException('Invalid IP address');
        }

        $this->signerName = trim($signerName);
        $this->signerEmail = strtolower(trim($signerEmail));
        $this->signatureImage = $signatureImage;
        $this->signedAt = $signedAt;
        $this->ipAddress = $ipAddress;
        $this->metadata = $metadata;
    }

    public static function create(
        string $signerName,
        string $signerEmail,
        ?string $signatureImage = null,
        ?string $ipAddress = null
    ): self {
        return new self(
            $signerName,
            $signerEmail,
            $signatureImage,
            new DateTimeImmutable(),
            $ipAddress ?? '0.0.0.0',
            []
        );
    }

    public function getSignerName(): string
    {
        return $this->signerName;
    }

    public function getSignerEmail(): string
    {
        return $this->signerEmail;
    }

    public function getSignatureImage(): ?string
    {
        return $this->signatureImage;
    }

    public function getSignedAt(): DateTimeImmutable
    {
        return $this->signedAt;
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function hasSignatureImage(): bool
    {
        return $this->signatureImage !== null;
    }

    public function withMetadata(array $metadata): self
    {
        return new self(
            $this->signerName,
            $this->signerEmail,
            $this->signatureImage,
            $this->signedAt,
            $this->ipAddress,
            array_merge($this->metadata, $metadata)
        );
    }

    public function toArray(): array
    {
        return [
            'signer_name' => $this->signerName,
            'signer_email' => $this->signerEmail,
            'has_signature_image' => $this->hasSignatureImage(),
            'signed_at' => $this->signedAt->format('Y-m-d H:i:s'),
            'ip_address' => $this->ipAddress,
            'metadata' => $this->metadata,
        ];
    }

    public function verify(string $email): bool
    {
        return $this->signerEmail === strtolower(trim($email));
    }
}
