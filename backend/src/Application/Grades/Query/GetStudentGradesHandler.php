<?php

declare(strict_types=1);

namespace App\Application\Grades\Query;

use App\Application\Grades\DTO\GradeRecordDTO;
use App\Domain\Grades\Repository\GradeRecordRepositoryInterface;
use App\Infrastructure\Cache\CacheService;
use App\Repository\StudentRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetStudentGradesHandler
{
    public function __construct(
        private readonly GradeRecordRepositoryInterface $gradeRepository,
        private readonly StudentRepository $studentRepository,
        private readonly CacheService $cache
    ) {}

    public function __invoke(GetStudentGradesQuery $query): array
    {
        $student = $this->studentRepository->find($query->studentId);

        if (!$student) {
            throw new \InvalidArgumentException('Student not found');
        }

        $academicYear = $query->academicYear ?? (int) date('Y');

        // Use cache for grade retrieval
        return $this->cache->getStudentGrades(
            $query->studentId,
            $academicYear,
            function () use ($student, $query, $academicYear): array {
                if ($query->bimester) {
                    $records = $this->gradeRepository->findByStudentAndBimester(
                        $student,
                        $query->bimester,
                        $academicYear
                    );
                } else {
                    // Get all bimesters for the year
                    $records = [];
                    for ($bim = 1; $bim <= 4; $bim++) {
                        $bimRecords = $this->gradeRepository->findByStudentAndBimester(
                            $student,
                            $bim,
                            $academicYear
                        );
                        $records = array_merge($records, $bimRecords);
                    }
                }

                return array_map(
                    fn($record) => GradeRecordDTO::fromEntity($record),
                    $records
                );
            }
        );
    }
}
