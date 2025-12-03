<?php

declare(strict_types=1);

namespace App\Service\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use OTPHP\TOTP;
use ParagonIE\ConstantTime\Base32;

final readonly class MFAService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private string $appName = 'School Platform',
    ) {}

    /**
     * Genera un secreto TOTP para un usuario
     */
    public function generateSecret(User $user): string
    {
        $totp = TOTP::create();
        $secret = $totp->getSecret();

        // Guardar secreto encriptado en la base de datos
        $user->setMfaSecret($secret);
        $user->setMfaEnabled(false); // Se activará después de verificar

        $this->entityManager->flush();

        return $secret;
    }

    /**
     * Genera URI para QR code
     */
    public function getQRCodeUri(User $user): string
    {
        $secret = $user->getMfaSecret();
        if (!$secret) {
            throw new \RuntimeException('MFA secret not generated');
        }

        $totp = TOTP::create($secret);
        $totp->setLabel($user->getEmail());
        $totp->setIssuer($this->appName);

        return $totp->getProvisioningUri();
    }

    /**
     * Verifica un código TOTP
     */
    public function verifyCode(User $user, string $code): bool
    {
        $secret = $user->getMfaSecret();
        if (!$secret) {
            return false;
        }

        $totp = TOTP::create($secret);

        // Verificar código con ventana de tiempo de ±1 período (30 segundos)
        return $totp->verify($code, null, 1);
    }

    /**
     * Activa MFA para un usuario después de verificar el código
     */
    public function enableMFA(User $user, string $verificationCode): bool
    {
        if (!$this->verifyCode($user, $verificationCode)) {
            return false;
        }

        $user->setMfaEnabled(true);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Desactiva MFA para un usuario
     */
    public function disableMFA(User $user): void
    {
        $user->setMfaEnabled(false);
        $user->setMfaSecret(null);
        $this->entityManager->flush();
    }

    /**
     * Genera códigos de respaldo
     */
    public function generateBackupCodes(User $user, int $count = 10): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = bin2hex(random_bytes(4)); // 8 caracteres hexadecimales
        }

        // Guardar códigos hasheados
        $hashedCodes = array_map(
            fn($code) => password_hash($code, PASSWORD_ARGON2ID),
            $codes
        );

        $user->setMfaBackupCodes($hashedCodes);
        $this->entityManager->flush();

        return $codes; // Retornar códigos sin hashear para mostrar al usuario
    }

    /**
     * Verifica un código de respaldo
     */
    public function verifyBackupCode(User $user, string $code): bool
    {
        $backupCodes = $user->getMfaBackupCodes() ?? [];

        foreach ($backupCodes as $index => $hashedCode) {
            if (password_verify($code, $hashedCode)) {
                // Remover código usado
                unset($backupCodes[$index]);
                $user->setMfaBackupCodes(array_values($backupCodes));
                $this->entityManager->flush();

                return true;
            }
        }

        return false;
    }
}
