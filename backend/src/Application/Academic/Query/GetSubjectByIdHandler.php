<?php

declare(strict_types=1);

namespace App\Application\Academic\Query;

use App\Entity\Subject;
use App\Repository\SubjectRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetSubjectByIdHandler
{
    public function __construct(
        private readonly SubjectRepository $subjectRepository
    ) {}

    public function __invoke(GetSubjectByIdQuery $query): ?Subject
    {
        return $this->subjectRepository->find($query->id);
    }
}
