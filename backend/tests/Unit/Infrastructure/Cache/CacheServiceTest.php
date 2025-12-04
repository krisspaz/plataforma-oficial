<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Cache;

use App\Infrastructure\Cache\CacheService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CacheServiceTest extends TestCase
{
    private TagAwareCacheInterface $gradesCache;
    private TagAwareCacheInterface $announcementsCache;
    private TagAwareCacheInterface $calendarCache;
    private TagAwareCacheInterface $paymentsCache;
    private CacheInterface $cache;
    private LoggerInterface $logger;
    private CacheService $service;

    protected function setUp(): void
    {
        $this->gradesCache = $this->createMock(TagAwareCacheInterface::class);
        $this->announcementsCache = $this->createMock(TagAwareCacheInterface::class);
        $this->calendarCache = $this->createMock(TagAwareCacheInterface::class);
        $this->paymentsCache = $this->createMock(TagAwareCacheInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->service = new CacheService(
            $this->gradesCache,
            $this->announcementsCache,
            $this->calendarCache,
            $this->paymentsCache,
            $this->cache,
            $this->logger
        );
    }

    public function testGetStudentGradesCachesResult(): void
    {
        $studentId = 1;
        $year = 2025;
        $expectedData = [['grade' => 85]];

        $this->gradesCache
            ->expects($this->once())
            ->method('get')
            ->willReturn($expectedData);

        $result = $this->service->getStudentGrades($studentId, $year, fn() => $expectedData);

        $this->assertSame($expectedData, $result);
    }

    public function testInvalidateStudentGradesCallsInvalidateTags(): void
    {
        $studentId = 1;

        $this->gradesCache
            ->expects($this->once())
            ->method('invalidateTags')
            ->with(['student_1']);

        $this->service->invalidateStudentGrades($studentId);
    }

    public function testGetAnnouncementsCachesResult(): void
    {
        $type = 'general';
        $expectedData = [['title' => 'Test']];

        $this->announcementsCache
            ->expects($this->once())
            ->method('get')
            ->willReturn($expectedData);

        $result = $this->service->getAnnouncements($type, fn() => $expectedData);

        $this->assertSame($expectedData, $result);
    }

    public function testInvalidateAnnouncementsWithType(): void
    {
        $this->announcementsCache
            ->expects($this->once())
            ->method('invalidateTags')
            ->with(['type_general']);

        $this->service->invalidateAnnouncements('general');
    }

    public function testInvalidateAnnouncementsWithoutType(): void
    {
        $this->announcementsCache
            ->expects($this->once())
            ->method('invalidateTags')
            ->with(['announcements']);

        $this->service->invalidateAnnouncements();
    }

    public function testGetCalendarEventsCachesResult(): void
    {
        $expectedData = [['title' => 'Event']];

        $this->calendarCache
            ->expects($this->once())
            ->method('get')
            ->willReturn($expectedData);

        $result = $this->service->getCalendarEvents('2025-01-01', '2025-01-31', fn() => $expectedData);

        $this->assertSame($expectedData, $result);
    }

    public function testGetDebtorsReportCachesResult(): void
    {
        $expectedData = [['student' => 'Test']];

        $this->paymentsCache
            ->expects($this->once())
            ->method('get')
            ->willReturn($expectedData);

        $result = $this->service->getDebtorsReport(null, fn() => $expectedData);

        $this->assertSame($expectedData, $result);
    }

    public function testClearAllInvalidatesAllCaches(): void
    {
        $this->gradesCache->expects($this->once())->method('invalidateTags');
        $this->announcementsCache->expects($this->once())->method('invalidateTags');
        $this->calendarCache->expects($this->once())->method('invalidateTags');
        $this->paymentsCache->expects($this->once())->method('invalidateTags');

        $this->service->clearAll();
    }

    public function testGetStatsReturnsActiveStatus(): void
    {
        $stats = $this->service->getStats();

        $this->assertArrayHasKey('grades', $stats);
        $this->assertArrayHasKey('announcements', $stats);
        $this->assertArrayHasKey('calendar', $stats);
        $this->assertArrayHasKey('payments', $stats);
    }
}
