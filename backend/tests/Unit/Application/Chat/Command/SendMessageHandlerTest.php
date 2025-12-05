<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Chat\Command;

use App\Application\Chat\Command\SendMessageCommand;
use App\Application\Chat\Command\SendMessageHandler;
use App\Entity\ChatMessage;
use App\Entity\ChatRoom;
use App\Entity\User;
use App\Repository\ChatRoomRepository;
use App\Repository\UserRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SendMessageHandlerTest extends TestCase
{
    private MockObject|ChatRoomRepository $chatRoomRepository;
    private MockObject|UserRepository $userRepository;
    private MockObject|NotificationService $notificationService;
    private MockObject|EntityManagerInterface $entityManager;
    private SendMessageHandler $handler;

    protected function setUp(): void
    {
        $this->chatRoomRepository = $this->createMock(ChatRoomRepository::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->notificationService = $this->createMock(NotificationService::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->handler = new SendMessageHandler(
            $this->chatRoomRepository,
            $this->userRepository,
            $this->notificationService,
            $this->entityManager
        );
    }

    public function testInvokeSendsMessageSuccessfully(): void
    {
        $sender = $this->createMock(User::class);
        $sender->method('getId')->willReturn(1);
        $sender->method('getFullName')->willReturn('Test User');

        $room = $this->createMock(ChatRoom::class);
        $room->method('isParticipant')->with(1)->willReturn(true);
        $room->method('getParticipants')->willReturn([1, 2]);

        $recipient = $this->createMock(User::class);

        $this->chatRoomRepository->method('find')->with(1)->willReturn($room);
        $this->userRepository->method('find')->with(2)->willReturn($recipient);

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $this->notificationService
            ->expects($this->once())
            ->method('notifyNewMessage');

        $command = new SendMessageCommand(
            roomId: 1,
            sender: $sender,
            content: 'Hello World!'
        );

        $result = ($this->handler)($command);

        $this->assertArrayHasKey('message', $result);
        $this->assertEquals(201, $result['code']);
    }

    public function testInvokeReturns404WhenRoomNotFound(): void
    {
        $sender = $this->createMock(User::class);
        $this->chatRoomRepository->method('find')->with(999)->willReturn(null);

        $command = new SendMessageCommand(roomId: 999, sender: $sender, content: 'Test');
        $result = ($this->handler)($command);

        $this->assertEquals('Room not found', $result['error']);
        $this->assertEquals(404, $result['code']);
    }

    public function testInvokeReturns403WhenSenderNotParticipant(): void
    {
        $sender = $this->createMock(User::class);
        $sender->method('getId')->willReturn(1);

        $room = $this->createMock(ChatRoom::class);
        $room->method('isParticipant')->with(1)->willReturn(false);

        $this->chatRoomRepository->method('find')->willReturn($room);

        $command = new SendMessageCommand(roomId: 1, sender: $sender, content: 'Test');
        $result = ($this->handler)($command);

        $this->assertEquals('Unauthorized', $result['error']);
        $this->assertEquals(403, $result['code']);
    }
}
