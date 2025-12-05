<?php

declare(strict_types=1);

namespace App\Application\Enrollment\Command;

/**
 * Command to create a new enrollment
 */
final class CreateEnrollmentCommand
{
    public function __construct(
        public readonly int $studentId,
        public readonly int $sectionId
    ) {}
}
