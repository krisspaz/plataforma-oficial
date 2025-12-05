<?php

declare(strict_types=1);

namespace App\Application\Enrollment\Query;

use App\Repository\EnrollmentRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetEnrollmentsHandler
{
    public function __construct(
        private readonly EnrollmentRepository $enrollmentRepository
    ) {}

    public function __invoke(GetEnrollmentsQuery $query): array
    {
        return $this->enrollmentRepository->findActiveByYear($query->year);
    }
}
