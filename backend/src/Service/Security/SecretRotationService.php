<?php

declare(strict_types=1);

namespace App\Service\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;

final readonly class SecretRotationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Filesystem $filesystem,
        private string $projectDir,
    ) {}

    /**
     * Rota las claves JWT (debe ejecutarse mensualmente)
     */
    public function rotateJWTKeys(): array
    {
        $keysDir = $this->projectDir . '/config/jwt';

        // Backup de claves antiguas
        $timestamp = date('Y-m-d_H-i-s');
        $backupDir = $keysDir . '/backup_' . $timestamp;
        $this->filesystem->mkdir($backupDir);

        if (file_exists($keysDir . '/private.pem')) {
            $this->filesystem->copy(
                $keysDir . '/private.pem',
                $backupDir . '/private.pem'
            );
        }

        if (file_exists($keysDir . '/public.pem')) {
            $this->filesystem->copy(
                $keysDir . '/public.pem',
                $backupDir . '/public.pem'
            );
        }

        // Generar nuevas claves
        $passphrase = bin2hex(random_bytes(32));

        $config = [
            'private_key_bits' => 4096,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $privateKey = openssl_pkey_new($config);

        // Exportar clave privada
        openssl_pkey_export($privateKey, $privateKeyPem, $passphrase);
        file_put_contents($keysDir . '/private.pem', $privateKeyPem);

        // Exportar clave pública
        $publicKeyDetails = openssl_pkey_get_details($privateKey);
        file_put_contents($keysDir . '/public.pem', $publicKeyDetails['key']);

        // Actualizar .env con nueva passphrase
        $this->updateEnvVariable('JWT_PASSPHRASE', $passphrase);

        return [
            'status' => 'success',
            'backup_location' => $backupDir,
            'rotated_at' => $timestamp,
            'next_rotation' => date('Y-m-d', strtotime('+30 days'))
        ];
    }

    /**
     * Rota el APP_SECRET
     */
    public function rotateAppSecret(): string
    {
        $newSecret = bin2hex(random_bytes(32));
        $this->updateEnvVariable('APP_SECRET', $newSecret);

        return $newSecret;
    }

    /**
     * Rota API keys de servicios externos
     */
    public function rotateExternalAPIKeys(): array
    {
        $rotated = [];

        // Mercure JWT Secret
        $mercureSecret = bin2hex(random_bytes(32));
        $this->updateEnvVariable('MERCURE_JWT_SECRET', $mercureSecret);
        $rotated['mercure'] = true;

        // Aquí se agregarían rotaciones para otros servicios
        // OpenAI, AWS, Firebase, etc.

        return $rotated;
    }

    /**
     * Actualiza una variable en el archivo .env
     */
    private function updateEnvVariable(string $key, string $value): void
    {
        $envFile = $this->projectDir . '/.env';
        $envContent = file_get_contents($envFile);

        // Buscar y reemplazar la variable
        $pattern = "/^{$key}=.*/m";
        $replacement = "{$key}={$value}";

        if (preg_match($pattern, $envContent)) {
            $newContent = preg_replace($pattern, $replacement, $envContent);
        } else {
            // Si no existe, agregar al final
            $newContent = $envContent . "\n{$replacement}";
        }

        file_put_contents($envFile, $newContent);
    }

    /**
     * Verifica si es necesario rotar secretos
     */
    public function shouldRotate(): bool
    {
        $lastRotation = $this->getLastRotationDate();

        if (!$lastRotation) {
            return true;
        }

        $daysSinceRotation = (new \DateTime())->diff($lastRotation)->days;

        return $daysSinceRotation >= 30;
    }

    /**
     * Obtiene la fecha de la última rotación
     */
    private function getLastRotationDate(): ?\DateTime
    {
        $keysDir = $this->projectDir . '/config/jwt';

        if (!file_exists($keysDir . '/private.pem')) {
            return null;
        }

        $timestamp = filemtime($keysDir . '/private.pem');
        return new \DateTime('@' . $timestamp);
    }
}
