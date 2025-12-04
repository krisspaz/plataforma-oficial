<?php

declare(strict_types=1);

namespace App\Application\Grades\Command;

use App\Domain\Grades\Entity\BimesterClosure;
use App\Domain\Grades\Repository\BimesterClosureRepositoryInterface;
use App\Repository\GradeRepository;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CloseBimesterHandler
{
    public function __construct(
        private readonly BimesterClosureRepositoryInterface $closureRepository,
        private readonly GradeRepository $gradeRepository,
        private readonly UserRepository $userRepository
    ) {}

    public function __invoke(CloseBimesterCommand $command): void
    {
        $grade = $this->gradeRepository->find($command->gradeId);
        $user = $this->userRepository->find($command->userId);

        if (!$grade || !$user) {
            throw new \InvalidArgumentException('Invalid grade or user ID');
        }

        $closure = $this->closureRepository->find($grade, $command->bimester, $command->academicYear);

        if ($closure) {
            $closure->close($user);
        } else {
            $closure = new BimesterClosure($grade, $command->bimester, $command->academicYear);
            $closure->close($user);
        }

        $this->closureRepository->save($closure);
    }
}
