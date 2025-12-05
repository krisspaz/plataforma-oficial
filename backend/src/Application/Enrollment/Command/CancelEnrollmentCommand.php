<?php

declare(strict_types=1);

namespace App\Application\Enrollment\Command;

/**
 * Command to cancel an enrollment
 */
final class CancelEnrollmentCommand
{
    public function __construct(
        public readonly int $enrollmentId
    ) {}
}
