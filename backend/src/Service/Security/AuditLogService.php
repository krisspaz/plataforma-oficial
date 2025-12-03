<?php

declare(strict_types=1);

namespace App\Service\Security;

use App\Entity\AuditLog;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

final readonly class AuditLogService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack,
        private Security $security,
    ) {}

    /**
     * Registra un evento de auditoría
     */
    public function log(
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?array $oldData = null,
        ?array $newData = null,
        string $severity = 'info'
    ): void {
        $request = $this->requestStack->getCurrentRequest();
        $user = $this->security->getUser();

        $auditLog = new AuditLog();
        $auditLog->setAction($action);
        $auditLog->setEntityType($entityType);
        $auditLog->setEntityId($entityId);
        $auditLog->setOldData($oldData);
        $auditLog->setNewData($newData);
        $auditLog->setSeverity($severity);
        $auditLog->setUserId($user?->getId());
        $auditLog->setUserEmail($user?->getEmail());
        $auditLog->setIpAddress($request?->getClientIp());
        $auditLog->setUserAgent($request?->headers->get('User-Agent'));
        $auditLog->setCreatedAt(new \DateTimeImmutable());

        // Generar firma digital para inmutabilidad
        $auditLog->setSignature($this->generateSignature($auditLog));

        $this->entityManager->persist($auditLog);
        $this->entityManager->flush();
    }

    /**
     * Registra acceso a datos sensibles
     */
    public function logDataAccess(string $entityType, int $entityId, string $reason = ''): void
    {
        $this->log(
            action: 'data_access',
            entityType: $entityType,
            entityId: $entityId,
            newData: ['reason' => $reason],
            severity: 'info'
        );
    }

    /**
     * Registra cambios de permisos
     */
    public function logPermissionChange(User $user, array $oldRoles, array $newRoles): void
    {
        $this->log(
            action: 'permission_change',
            entityType: 'User',
            entityId: $user->getId(),
            oldData: ['roles' => $oldRoles],
            newData: ['roles' => $newRoles],
            severity: 'warning'
        );
    }

    /**
     * Registra intentos de login fallidos
     */
    public function logFailedLogin(string $email, string $reason): void
    {
        $this->log(
            action: 'login_failed',
            entityType: 'User',
            newData: [
                'email' => $email,
                'reason' => $reason
            ],
            severity: 'warning'
        );
    }

    /**
     * Registra login exitoso
     */
    public function logSuccessfulLogin(User $user): void
    {
        $this->log(
            action: 'login_success',
            entityType: 'User',
            entityId: $user->getId(),
            severity: 'info'
        );
    }

    /**
     * Registra exportación de datos
     */
    public function logDataExport(string $entityType, array $filters, int $recordCount): void
    {
        $this->log(
            action: 'data_export',
            entityType: $entityType,
            newData: [
                'filters' => $filters,
                'record_count' => $recordCount
            ],
            severity: 'warning'
        );
    }

    /**
     * Registra eliminación de datos
     */
    public function logDataDeletion(string $entityType, int $entityId, array $data): void
    {
        $this->log(
            action: 'data_deletion',
            entityType: $entityType,
            entityId: $entityId,
            oldData: $data,
            severity: 'critical'
        );
    }

    /**
     * Genera firma digital para el log (inmutabilidad)
     */
    private function generateSignature(AuditLog $log): string
    {
        $data = sprintf(
            '%s|%s|%s|%s|%s',
            $log->getAction(),
            $log->getEntityType(),
            $log->getEntityId() ?? '',
            $log->getUserId() ?? '',
            $log->getCreatedAt()->format('Y-m-d H:i:s')
        );

        return hash_hmac('sha256', $data, $_ENV['APP_SECRET']);
    }

    /**
     * Verifica la integridad de un log
     */
    public function verifyIntegrity(AuditLog $log): bool
    {
        $expectedSignature = $this->generateSignature($log);
        return hash_equals($expectedSignature, $log->getSignature());
    }
}
