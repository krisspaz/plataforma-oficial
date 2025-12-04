<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Grades;

use App\Domain\Grades\Entity\BimesterClosure;
use App\Entity\Grade;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class BimesterClosureTest extends TestCase
{
    public function testCreateBimesterClosure(): void
    {
        $grade = $this->createMock(Grade::class);

        $closure = new BimesterClosure($grade, 1, 2025);

        $this->assertNotNull($closure->getId());
        $this->assertSame($grade, $closure->getGrade());
        $this->assertSame(1, $closure->getBimester());
        $this->assertSame(2025, $closure->getAcademicYear());
        $this->assertFalse($closure->isClosed());
    }

    public function testCloseBimester(): void
    {
        $grade = $this->createMock(Grade::class);
        $user = $this->createMock(User::class);

        $closure = new BimesterClosure($grade, 2, 2025);
        $closure->close($user);

        $this->assertTrue($closure->isClosed());
        $this->assertSame($user, $closure->getClosedBy());
        $this->assertNotNull($closure->getClosedAt());
    }

    public function testCannotCloseAlreadyClosed(): void
    {
        $this->expectException(\DomainException::class);

        $grade = $this->createMock(Grade::class);
        $user = $this->createMock(User::class);

        $closure = new BimesterClosure($grade, 1, 2025);
        $closure->close($user);
        $closure->close($user); // Should throw
    }

    public function testReopenBimester(): void
    {
        $grade = $this->createMock(Grade::class);
        $closeUser = $this->createMock(User::class);
        $reopenUser = $this->createMock(User::class);

        $closure = new BimesterClosure($grade, 1, 2025);
        $closure->close($closeUser);
        $closure->reopen($reopenUser, 'Need to fix grades');

        $this->assertFalse($closure->isClosed());
        $this->assertSame($reopenUser, $closure->getReopenedBy());
        $this->assertSame('Need to fix grades', $closure->getReopenReason());
        $this->assertNotNull($closure->getReopenedAt());
    }

    public function testCannotReopenIfNotClosed(): void
    {
        $this->expectException(\DomainException::class);

        $grade = $this->createMock(Grade::class);
        $user = $this->createMock(User::class);

        $closure = new BimesterClosure($grade, 1, 2025);
        $closure->reopen($user, 'Reason'); // Should throw
    }

    public function testInvalidBimester(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $grade = $this->createMock(Grade::class);
        new BimesterClosure($grade, 5, 2025);
    }
}
