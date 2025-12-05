<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\TwoFactorAuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/2fa')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class TwoFactorController extends AbstractController
{
    public function __construct(
        private readonly TwoFactorAuthService $twoFactorService
    ) {}

    /**
     * Get 2FA status for current user
     */
    #[Route('/status', name: 'api_2fa_status', methods: ['GET'])]
    public function status(): JsonResponse
    {
        $user = $this->getUser();

        return $this->json([
            'enabled' => $this->twoFactorService->is2FAEnabled($user),
            'hasBackupCodes' => !empty($user->getBackupCodes()),
        ]);
    }

    /**
     * Start 2FA setup - returns QR code
     */
    #[Route('/setup', name: 'api_2fa_setup', methods: ['POST'])]
    public function setup(): JsonResponse
    {
        $user = $this->getUser();

        if ($this->twoFactorService->is2FAEnabled($user)) {
            return $this->json([
                'error' => '2FA is already enabled'
            ], Response::HTTP_BAD_REQUEST);
        }

        $setupData = $this->twoFactorService->enable2FA($user);

        return $this->json([
            'qrCode' => $setupData['qrCode'],
            'manualEntry' => $setupData['manualEntry'],
            'message' => 'Escanea el código QR con tu aplicación de autenticación'
        ]);
    }

    /**
     * Verify 2FA code and complete setup
     */
    #[Route('/verify', name: 'api_2fa_verify', methods: ['POST'])]
    public function verify(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        $code = $data['code'] ?? '';

        if (empty($code)) {
            return $this->json([
                'error' => 'Code is required'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (!$this->twoFactorService->verify2FA($user, $code)) {
            return $this->json([
                'error' => 'Invalid code'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Generate backup codes
        $backupCodes = $this->twoFactorService->generateBackupCodes($user);

        return $this->json([
            'success' => true,
            'message' => '2FA activado exitosamente',
            'backupCodes' => $backupCodes,
            'warning' => 'Guarda estos códigos de respaldo en un lugar seguro. Solo se mostrarán una vez.'
        ]);
    }

    /**
     * Disable 2FA
     */
    #[Route('/disable', name: 'api_2fa_disable', methods: ['POST'])]
    public function disable(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        $code = $data['code'] ?? '';
        $password = $data['password'] ?? '';

        // Require current 2FA code or password for security
        if (empty($code) && empty($password)) {
            return $this->json([
                'error' => '2FA code or password is required'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Verify 2FA code if provided
        if (!empty($code) && !$this->twoFactorService->verify2FA($user, $code)) {
            return $this->json([
                'error' => 'Invalid 2FA code'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $this->twoFactorService->disable2FA($user);

        return $this->json([
            'success' => true,
            'message' => '2FA desactivado'
        ]);
    }

    /**
     * Regenerate backup codes
     */
    #[Route('/backup-codes', name: 'api_2fa_backup_codes', methods: ['POST'])]
    public function regenerateBackupCodes(Request $request): JsonResponse
    {
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true);
        $code = $data['code'] ?? '';

        if (!$this->twoFactorService->verify2FA($user, $code)) {
            return $this->json([
                'error' => 'Invalid 2FA code'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $backupCodes = $this->twoFactorService->generateBackupCodes($user);

        return $this->json([
            'backupCodes' => $backupCodes,
            'warning' => 'Los códigos anteriores han sido invalidados'
        ]);
    }
}
