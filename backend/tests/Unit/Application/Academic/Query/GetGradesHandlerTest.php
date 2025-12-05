<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Academic\Query;

use App\Application\Academic\Query\GetGradesHandler;
use App\Application\Academic\Query\GetGradesQuery;
use App\Entity\Grade;
use App\Repository\GradeRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetGradesHandlerTest extends TestCase
{
    private MockObject|GradeRepository $gradeRepository;
    private GetGradesHandler $handler;

    protected function setUp(): void
    {
        $this->gradeRepository = $this->createMock(GradeRepository::class);
        $this->handler = new GetGradesHandler($this->gradeRepository);
    }

    public function testInvokeReturnsAllGrades(): void
    {
        $grades = [
            $this->createMock(Grade::class),
            $this->createMock(Grade::class),
            $this->createMock(Grade::class),
        ];

        $this->gradeRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($grades);

        $query = new GetGradesQuery();
        $result = ($this->handler)($query);

        $this->assertCount(3, $result);
    }

    public function testInvokeReturnsEmptyArrayWhenNoGrades(): void
    {
        $this->gradeRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $query = new GetGradesQuery();
        $result = ($this->handler)($query);

        $this->assertEmpty($result);
    }
}
