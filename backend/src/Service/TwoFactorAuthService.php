<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;

/**
 * Two-Factor Authentication Service
 * Handles 2FA setup, verification, and management
 */
class TwoFactorAuthService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TotpAuthenticatorInterface $totpAuthenticator
    ) {}

    /**
     * Enable 2FA for a user and return QR code data
     */
    public function enable2FA(User $user): array
    {
        // Generate secret if not exists
        if (!$user->getTotpSecret()) {
            $secret = $this->totpAuthenticator->generateSecret();
            $user->setTotpSecret($secret);
        }

        // Generate QR code URL
        $qrCodeUrl = $this->totpAuthenticator->getQRContent($user);

        // Generate QR code image
        $qrCode = QrCode::create($qrCodeUrl)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::High)
            ->setSize(300)
            ->setMargin(10);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        $qrCodeBase64 = base64_encode($result->getString());

        return [
            'secret' => $user->getTotpSecret(),
            'qrCode' => 'data:image/png;base64,' . $qrCodeBase64,
            'manualEntry' => $qrCodeUrl,
        ];
    }

    /**
     * Verify 2FA code and complete setup
     */
    public function verify2FA(User $user, string $code): bool
    {
        if (!$this->totpAuthenticator->checkCode($user, $code)) {
            return false;
        }

        // Enable 2FA for user
        $user->setTwoFactorEnabled(true);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Disable 2FA for a user
     */
    public function disable2FA(User $user): void
    {
        $user->setTwoFactorEnabled(false);
        $user->setTotpSecret(null);
        $this->entityManager->flush();
    }

    /**
     * Generate backup codes for recovery
     */
    public function generateBackupCodes(User $user): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4)));
        }

        // Hash and store codes
        $hashedCodes = array_map(fn($code) => password_hash($code, PASSWORD_BCRYPT), $codes);
        $user->setBackupCodes($hashedCodes);
        $this->entityManager->flush();

        return $codes; // Return plain codes to show user once
    }

    /**
     * Verify a backup code
     */
    public function verifyBackupCode(User $user, string $code): bool
    {
        $backupCodes = $user->getBackupCodes() ?? [];

        foreach ($backupCodes as $index => $hashedCode) {
            if (password_verify(strtoupper($code), $hashedCode)) {
                // Remove used code
                unset($backupCodes[$index]);
                $user->setBackupCodes(array_values($backupCodes));
                $this->entityManager->flush();
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has 2FA enabled
     */
    public function is2FAEnabled(User $user): bool
    {
        return $user->isTwoFactorEnabled() && $user->getTotpSecret() !== null;
    }
}
