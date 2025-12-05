<?php

declare(strict_types=1);

namespace App\Application\Academic\Query;

use App\Repository\GradeRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[AsMessageHandler]
final class GetGradesHandler
{
    public function __construct(
        private readonly GradeRepository $gradeRepository,
        private readonly CacheInterface $gradesCache
    ) {}

    public function __invoke(GetGradesQuery $query): array
    {
        return $this->gradesCache->get('grades_all', function (ItemInterface $item) {
            $item->expiresAfter(3600); // 1 hour
            return $this->gradeRepository->findAll();
        });
    }
}
