<?php

declare(strict_types=1);

namespace App\Application\Teacher\Query;

use App\Repository\TeacherRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[AsMessageHandler]
final class GetTeachersHandler
{
    public function __construct(
        private readonly TeacherRepository $teacherRepository,
        private readonly CacheInterface $standardCache
    ) {}

    public function __invoke(GetTeachersQuery $query): array
    {
        return $this->standardCache->get('teachers_all', function (ItemInterface $item) {
            $item->expiresAfter(3600); // 1 hour
            return $this->teacherRepository->findAll();
        });
    }
}
