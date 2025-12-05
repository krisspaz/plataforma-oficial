<?php

declare(strict_types=1);

namespace App\Application\Enrollment\Query;

use App\Repository\EnrollmentRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetEnrollmentStatsByGradeHandler
{
    public function __construct(
        private readonly EnrollmentRepository $enrollmentRepository
    ) {}

    public function __invoke(GetEnrollmentStatsByGradeQuery $query): array
    {
        return $this->enrollmentRepository->getStatsByGrade($query->year);
    }
}
