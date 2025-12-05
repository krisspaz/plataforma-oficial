<?php

declare(strict_types=1);

namespace App\Application\Parent\Query;

use App\Entity\Guardian;
use App\Repository\ParentRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetParentByIdHandler
{
    public function __construct(
        private readonly ParentRepository $parentRepository
    ) {}

    public function __invoke(GetParentByIdQuery $query): ?Guardian
    {
        return $this->parentRepository->find($query->id);
    }
}
