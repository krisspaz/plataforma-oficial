<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Teacher\Query;

use App\Application\Teacher\Query\GetTeachersHandler;
use App\Application\Teacher\Query\GetTeachersQuery;
use App\Application\Teacher\Query\SearchTeachersHandler;
use App\Application\Teacher\Query\SearchTeachersQuery;
use App\Entity\Teacher;
use App\Repository\TeacherRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetTeachersHandlerTest extends TestCase
{
    private MockObject|TeacherRepository $teacherRepository;

    protected function setUp(): void
    {
        $this->teacherRepository = $this->createMock(TeacherRepository::class);
    }

    public function testGetTeachersReturnsAllTeachers(): void
    {
        $handler = new GetTeachersHandler($this->teacherRepository);

        $teachers = [
            $this->createMock(Teacher::class),
            $this->createMock(Teacher::class),
        ];

        $this->teacherRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($teachers);

        $query = new GetTeachersQuery();
        $result = ($handler)($query);

        $this->assertCount(2, $result);
    }

    public function testSearchTeachersReturnsMatchingTeachers(): void
    {
        $handler = new SearchTeachersHandler($this->teacherRepository);

        $teacher = $this->createMock(Teacher::class);

        $this->teacherRepository
            ->expects($this->once())
            ->method('search')
            ->with('John')
            ->willReturn([$teacher]);

        $query = new SearchTeachersQuery('John');
        $result = ($handler)($query);

        $this->assertCount(1, $result);
    }

    public function testSearchTeachersReturnsEmptyForNoMatch(): void
    {
        $handler = new SearchTeachersHandler($this->teacherRepository);

        $this->teacherRepository
            ->expects($this->once())
            ->method('search')
            ->with('NonExistent')
            ->willReturn([]);

        $query = new SearchTeachersQuery('NonExistent');
        $result = ($handler)($query);

        $this->assertEmpty($result);
    }
}
