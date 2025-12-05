<?php

declare(strict_types=1);

namespace App\Application\Enrollment\Command;

/**
 * Command to update an enrollment
 */
final class UpdateEnrollmentCommand
{
    public function __construct(
        public readonly int $enrollmentId,
        public readonly ?string $status = null,
        public readonly ?int $sectionId = null
    ) {}
}
