<?php

declare(strict_types=1);

namespace App\Infrastructure\EventListener;

use App\Domain\Student\Event\StudentCreatedEvent;
use Psr\Log\LoggerInterface;

final readonly class StudentCreatedEventListener
{
    public function __construct(
        private LoggerInterface $logger,
        // private MailerInterface $mailer, // Uncomment when mailer is configured
    ) {}

    public function onStudentCreated(StudentCreatedEvent $event): void
    {
        $this->logger->info('Student created', [
            'student_id' => $event->studentId->value,
            'email' => (string) $event->email,
            'occurred_on' => $event->occurredOn->format('Y-m-d H:i:s'),
        ]);

        // TODO: Send welcome email
        // $this->sendWelcomeEmail($event);

        // TODO: Create notification for parents
        // $this->notifyParents($event);

        // TODO: Add to student orientation schedule
        // $this->scheduleOrientation($event);
    }

    private function sendWelcomeEmail(StudentCreatedEvent $event): void
    {
        // Implementation for sending welcome email
        // with temporary password and login instructions
    }
}
