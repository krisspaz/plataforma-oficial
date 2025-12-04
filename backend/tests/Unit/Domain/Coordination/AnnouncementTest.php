<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Coordination;

use App\Domain\Coordination\Entity\Announcement;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class AnnouncementTest extends TestCase
{
    public function testCreateGeneralAnnouncement(): void
    {
        $author = $this->createMock(User::class);

        $announcement = new Announcement(
            'Test Title',
            'Test Content',
            'general',
            $author
        );

        $this->assertNotNull($announcement->getId());
        $this->assertSame('Test Title', $announcement->getTitle());
        $this->assertSame('Test Content', $announcement->getContent());
        $this->assertSame('general', $announcement->getType());
        $this->assertSame($author, $announcement->getAuthor());
        $this->assertNull($announcement->getExpiresAt());
        $this->assertNull($announcement->getTargetIds());
    }

    public function testCreateAnnouncementWithExpiration(): void
    {
        $author = $this->createMock(User::class);
        $expiresAt = new \DateTimeImmutable('+7 days');

        $announcement = new Announcement(
            'Expiring Announcement',
            'This will expire',
            'teachers',
            $author,
            $expiresAt
        );

        $this->assertSame($expiresAt, $announcement->getExpiresAt());
    }

    public function testCreateTargetedAnnouncement(): void
    {
        $author = $this->createMock(User::class);
        $targetIds = [1, 2, 3];

        $announcement = new Announcement(
            'Targeted',
            'For specific grades',
            'specific_grade',
            $author,
            null,
            $targetIds
        );

        $this->assertSame($targetIds, $announcement->getTargetIds());
    }

    public function testInvalidAnnouncementType(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $author = $this->createMock(User::class);
        new Announcement('Title', 'Content', 'invalid_type', $author);
    }

    public function testEmptyTitleThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $author = $this->createMock(User::class);
        new Announcement('', 'Content', 'general', $author);
    }
}
