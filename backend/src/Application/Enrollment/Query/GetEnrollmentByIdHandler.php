<?php

declare(strict_types=1);

namespace App\Application\Enrollment\Query;

use App\Entity\Enrollment;
use App\Repository\EnrollmentRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetEnrollmentByIdHandler
{
    public function __construct(
        private readonly EnrollmentRepository $enrollmentRepository
    ) {}

    public function __invoke(GetEnrollmentByIdQuery $query): ?Enrollment
    {
        return $this->enrollmentRepository->find($query->id);
    }
}
