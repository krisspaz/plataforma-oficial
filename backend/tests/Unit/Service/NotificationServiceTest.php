<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Notification;
use App\Entity\User;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class NotificationServiceTest extends TestCase
{
    private MockObject|EntityManagerInterface $entityManager;
    private MockObject|HubInterface $hub;
    private NotificationService $service;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->hub = $this->createMock(HubInterface::class);

        $this->service = new NotificationService($this->entityManager, $this->hub);
    }

    public function testCreateNotificationPersistsNotification(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Notification::class));

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->hub
            ->expects($this->once())
            ->method('publish')
            ->with($this->isInstanceOf(Update::class));

        $notification = $this->service->createNotification(
            $user,
            'Test Title',
            'Test Message',
            'info'
        );

        $this->assertInstanceOf(Notification::class, $notification);
    }

    public function testNotifyContractSignedCreatesCorrectNotification(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $this->entityManager->method('persist');
        $this->entityManager->method('flush');
        $this->hub->method('publish');

        $notification = $this->service->notifyContractSigned($user, 'CNT-001');

        $this->assertEquals('Contrato Firmado', $notification->getTitle());
        $this->assertStringContainsString('CNT-001', $notification->getMessage());
    }

    public function testNotifyNewMessageCreatesCorrectNotification(): void
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);

        $this->entityManager->method('persist');
        $this->entityManager->method('flush');
        $this->hub->method('publish');

        $notification = $this->service->notifyNewMessage($user, 'John Doe', 'Hello world...');

        $this->assertEquals('Nuevo mensaje de John Doe', $notification->getTitle());
        $this->assertEquals('Hello world...', $notification->getMessage());
    }
}
