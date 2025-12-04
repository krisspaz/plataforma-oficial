<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;

final class SecretRotationService
{
    private const ROTATION_INTERVAL_DAYS = 30;
    private const BACKUP_DIR = '/var/backups/secrets';

    public function __construct(
        private readonly string $projectDir,
        private readonly LoggerInterface $logger,
    ) {}

    public function shouldRotate(): bool
    {
        $lastRotationFile = $this->projectDir . '/var/cache/.last_rotation';
        
        if (!file_exists($lastRotationFile)) {
            return true;
        }

        $lastRotation = new \DateTime('@' . (int) file_get_contents($lastRotationFile));
        $now = new \DateTime();
        $diff = $now->diff($lastRotation);

        return $diff->days >= self::ROTATION_INTERVAL_DAYS;
    }

    public function getLastRotationDate(): ?\DateTime
    {
        $lastRotationFile = $this->projectDir . '/var/cache/.last_rotation';
        
        if (!file_exists($lastRotationFile)) {
            return null;
        }

        return new \DateTime('@' . (int) file_get_contents($lastRotationFile));
    }

    public function getDaysUntilNextRotation(): int
    {
        $lastRotation = $this->getLastRotationDate();
        
        if (!$lastRotation) {
            return 0;
        }

        $nextRotation = (clone $lastRotation)->modify('+' . self::ROTATION_INTERVAL_DAYS . ' days');
        $now = new \DateTime();
        $diff = $nextRotation->diff($now);

        return max(0, $diff->days);
    }

    public function rotateJWTKeys(): array
    {
        $jwtDir = $this->projectDir . '/config/jwt';
        $backupDir = self::BACKUP_DIR . '/jwt/' . date('Y-m-d_His');

        // Create backup
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0700, true);
        }

        if (file_exists($jwtDir . '/private.pem')) {
            copy($jwtDir . '/private.pem', $backupDir . '/private.pem');
            copy($jwtDir . '/public.pem', $backupDir . '/public.pem');
        }

        // Generate new keys
        $config = [
            'digest_alg' => 'sha256',
            'private_key_bits' => 4096,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $res = openssl_pkey_new($config);
        openssl_pkey_export($res, $privateKey, $_ENV['JWT_PASSPHRASE'] ?? 'changeme');
        $publicKey = openssl_pkey_get_details($res)['key'];

        file_put_contents($jwtDir . '/private.pem', $privateKey);
        file_put_contents($jwtDir . '/public.pem', $publicKey);

        chmod($jwtDir . '/private.pem', 0600);
        chmod($jwtDir . '/public.pem', 0644);

        $this->updateLastRotation();
        $this->logger->info('JWT keys rotated successfully', ['backup' => $backupDir]);

        return [
            'backup_location' => $backupDir,
            'keys' => ['private', 'public'],
            'next_rotation' => (new \DateTime())->modify('+' . self::ROTATION_INTERVAL_DAYS . ' days')->format('Y-m-d')
        ];
    }

    public function rotateAppSecret(): array
    {
        $envFile = $this->projectDir . '/.env.local';
        $oldSecret = $_ENV['APP_SECRET'] ?? null;
        $newSecret = bin2hex(random_bytes(32));

        // Update .env.local
        $envContent = file_exists($envFile) ? file_get_contents($envFile) : '';
        
        if (str_contains($envContent, 'APP_SECRET=')) {
            $envContent = preg_replace('/APP_SECRET=.*/', 'APP_SECRET=' . $newSecret, $envContent);
        } else {
            $envContent .= "\nAPP_SECRET=" . $newSecret . "\n";
        }

        file_put_contents($envFile, $envContent);

        $this->logger->info('APP_SECRET rotated successfully');

        return [
            'env_file' => $envFile,
            'old_secret' => $oldSecret,
            'new_secret' => $newSecret
        ];
    }

    public function rotateExternalAPIKeys(): array
    {
        // Placeholder for external API key rotation
        // This would integrate with services like Stripe, PayPal, etc.
        
        $results = [];

        // Example: Rotate Stripe keys (would need actual Stripe API integration)
        if (isset($_ENV['STRIPE_SECRET_KEY'])) {
            $results['stripe'] = [
                'rotated' => false,
                'message' => 'Manual rotation required via Stripe Dashboard'
            ];
        }

        $this->logger->info('External API keys rotation check completed', $results);

        return $results;
    }

    private function updateLastRotation(): void
    {
        $lastRotationFile = $this->projectDir . '/var/cache/.last_rotation';
        $cacheDir = dirname($lastRotationFile);

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        file_put_contents($lastRotationFile, time());
    }
}
