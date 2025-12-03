<?php

declare(strict_types=1);

namespace App\Domain\Student\Event;

use App\Domain\Student\ValueObject\StudentId;
use App\Domain\Student\ValueObject\Email;
use DateTimeImmutable;

final readonly class StudentCreatedEvent
{
    public function __construct(
        public StudentId $studentId,
        public Email $email,
        public string $firstName,
        public string $lastName,
        public DateTimeImmutable $occurredOn = new DateTimeImmutable()
    ) {}

    public function toArray(): array
    {
        return [
            'student_id' => $this->studentId->value,
            'email' => (string) $this->email,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'occurred_on' => $this->occurredOn->format('Y-m-d H:i:s'),
        ];
    }

    public function getEventName(): string
    {
        return 'student.created';
    }
}
