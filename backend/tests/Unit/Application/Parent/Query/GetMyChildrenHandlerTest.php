<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Parent\Query;

use App\Application\Parent\Query\GetMyChildrenHandler;
use App\Application\Parent\Query\GetMyChildrenQuery;
use App\Entity\Guardian;
use App\Entity\Student;
use App\Entity\User;
use App\Repository\ParentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetMyChildrenHandlerTest extends TestCase
{
    private MockObject|ParentRepository $parentRepository;
    private GetMyChildrenHandler $handler;

    protected function setUp(): void
    {
        $this->parentRepository = $this->createMock(ParentRepository::class);
        $this->handler = new GetMyChildrenHandler($this->parentRepository);
    }

    public function testInvokeReturnsChildrenForParent(): void
    {
        $user = $this->createMock(User::class);
        $student1 = $this->createMock(Student::class);
        $student2 = $this->createMock(Student::class);

        $parent = $this->createMock(Guardian::class);
        $parent->method('getStudents')->willReturn(new ArrayCollection([$student1, $student2]));

        $this->parentRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['user' => $user])
            ->willReturn($parent);

        $query = new GetMyChildrenQuery($user);
        $result = ($this->handler)($query);

        $this->assertArrayHasKey('children', $result);
        $this->assertEquals(2, $result['count']);
    }

    public function testInvokeReturnsNullWhenParentNotFound(): void
    {
        $user = $this->createMock(User::class);

        $this->parentRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['user' => $user])
            ->willReturn(null);

        $query = new GetMyChildrenQuery($user);
        $result = ($this->handler)($query);

        $this->assertNull($result);
    }

    public function testInvokeReturnsEmptyChildrenForParentWithNoChildren(): void
    {
        $user = $this->createMock(User::class);

        $parent = $this->createMock(Guardian::class);
        $parent->method('getStudents')->willReturn(new ArrayCollection([]));

        $this->parentRepository
            ->method('findOneBy')
            ->willReturn($parent);

        $query = new GetMyChildrenQuery($user);
        $result = ($this->handler)($query);

        $this->assertEquals(0, $result['count']);
    }
}
