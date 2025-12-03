<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function createNotification(
        User $user,
        string $title,
        string $message,
        string $type = 'info',
        ?array $data = null
    ): Notification {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setTitle($title);
        $notification->setMessage($message);
        $notification->setType($type);
        $notification->setData($data);
        $notification->setRead(false);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        // TODO: Send push notification via Firebase
        // $this->sendPushNotification($user, $title, $message);

        return $notification;
    }

    public function notifyEnrollment(User $user, string $studentName, string $gradeName): void
    {
        $this->createNotification(
            $user,
            'Nueva Inscripción',
            "El estudiante {$studentName} ha sido inscrito en {$gradeName}",
            'enrollment',
            ['studentName' => $studentName, 'gradeName' => $gradeName]
        );
    }

    public function notifyPayment(User $user, float $amount, string $status): void
    {
        $this->createNotification(
            $user,
            'Pago Registrado',
            "Se ha registrado un pago de Q{$amount}. Estado: {$status}",
            'payment',
            ['amount' => $amount, 'status' => $status]
        );
    }

    public function notifyPaymentDue(User $user, float $amount, \DateTime $dueDate): void
    {
        $this->createNotification(
            $user,
            'Pago Pendiente',
            "Tiene un pago pendiente de Q{$amount} con vencimiento el {$dueDate->format('d/m/Y')}",
            'payment_due',
            ['amount' => $amount, 'dueDate' => $dueDate->format('Y-m-d')]
        );
    }

    public function notifyContractSigned(User $user, string $contractNumber): void
    {
        $this->createNotification(
            $user,
            'Contrato Firmado',
            "El contrato {$contractNumber} ha sido firmado exitosamente",
            'contract',
            ['contractNumber' => $contractNumber]
        );
    }

    public function notifyAcademicRisk(User $user, string $studentName, string $riskLevel): void
    {
        $this->createNotification(
            $user,
            'Alerta Académica',
            "El estudiante {$studentName} presenta un nivel de riesgo académico: {$riskLevel}",
            'academic_risk',
            ['studentName' => $studentName, 'riskLevel' => $riskLevel]
        );
    }

    public function notifyNewMessage(User $user, string $senderName, string $preview): void
    {
        $this->createNotification(
            $user,
            'Nuevo Mensaje',
            "{$senderName}: {$preview}",
            'chat',
            ['senderName' => $senderName, 'preview' => $preview]
        );
    }

    public function markAsRead(Notification $notification): void
    {
        $notification->markAsRead();
        $this->entityManager->flush();
    }

    public function markAllAsRead(User $user): void
    {
        $notifications = $this->entityManager->getRepository(Notification::class)
            ->findBy(['user' => $user, 'read' => false]);

        foreach ($notifications as $notification) {
            $notification->markAsRead();
        }

        $this->entityManager->flush();
    }

    private function sendPushNotification(User $user, string $title, string $message): void
    {
        // TODO: Implement Firebase Cloud Messaging
        // This would send push notifications to mobile devices
    }
}
