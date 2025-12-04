<?php

declare(strict_types=1);

namespace App\Infrastructure\Cache;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

/**
 * Enhanced cache service with tag support and logging.
 */
final class CacheService
{
    private const TTL_SHORT = 300;      // 5 minutes
    private const TTL_STANDARD = 3600;  // 1 hour
    private const TTL_LONG = 86400;     // 24 hours

    public function __construct(
        private readonly TagAwareCacheInterface $gradesCache,
        private readonly TagAwareCacheInterface $announcementsCache,
        private readonly TagAwareCacheInterface $calendarCache,
        private readonly TagAwareCacheInterface $paymentsCache,
        private readonly CacheInterface $cache,
        private readonly ?LoggerInterface $logger = null
    ) {}

    // ==================== GRADES CACHING ====================

    /**
     * Get cached student grades.
     */
    public function getStudentGrades(int $studentId, int $year, callable $loader): array
    {
        $key = sprintf('student_grades_%d_%d', $studentId, $year);

        return $this->gradesCache->get($key, function (ItemInterface $item) use ($loader, $studentId) {
            $item->expiresAfter(self::TTL_STANDARD);
            $item->tag(['grades', "student_{$studentId}"]);

            $this->log('Cache MISS: student_grades', ['student' => $studentId]);

            return $loader();
        });
    }

    /**
     * Get cached grades by subject and bimester.
     */
    public function getSubjectGrades(int $subjectId, int $bimester, int $year, callable $loader): array
    {
        $key = sprintf('subject_grades_%d_%d_%d', $subjectId, $bimester, $year);

        return $this->gradesCache->get($key, function (ItemInterface $item) use ($loader, $subjectId) {
            $item->expiresAfter(self::TTL_STANDARD);
            $item->tag(['grades', "subject_{$subjectId}"]);

            return $loader();
        });
    }

    /**
     * Invalidate student grades cache.
     */
    public function invalidateStudentGrades(int $studentId): void
    {
        $this->gradesCache->invalidateTags(["student_{$studentId}"]);
        $this->log('Cache invalidated: student grades', ['student' => $studentId]);
    }

    /**
     * Invalidate all grades cache.
     */
    public function invalidateAllGrades(): void
    {
        $this->gradesCache->invalidateTags(['grades']);
        $this->log('Cache invalidated: all grades');
    }

    // ==================== ANNOUNCEMENTS CACHING ====================

    /**
     * Get cached announcements.
     */
    public function getAnnouncements(?string $type, callable $loader): array
    {
        $key = sprintf('announcements_%s', $type ?? 'all');

        return $this->announcementsCache->get($key, function (ItemInterface $item) use ($loader, $type) {
            $item->expiresAfter(self::TTL_SHORT);
            $item->tag(['announcements', $type ? "type_{$type}" : 'type_all']);

            $this->log('Cache MISS: announcements', ['type' => $type]);

            return $loader();
        });
    }

    /**
     * Invalidate announcements cache.
     */
    public function invalidateAnnouncements(?string $type = null): void
    {
        if ($type) {
            $this->announcementsCache->invalidateTags(["type_{$type}"]);
        } else {
            $this->announcementsCache->invalidateTags(['announcements']);
        }
        $this->log('Cache invalidated: announcements', ['type' => $type]);
    }

    // ==================== CALENDAR CACHING ====================

    /**
     * Get cached calendar events.
     */
    public function getCalendarEvents(string $startDate, string $endDate, callable $loader): array
    {
        $key = sprintf('calendar_%s_%s', $startDate, $endDate);

        return $this->calendarCache->get($key, function (ItemInterface $item) use ($loader) {
            $item->expiresAfter(self::TTL_STANDARD);
            $item->tag(['calendar']);

            $this->log('Cache MISS: calendar events');

            return $loader();
        });
    }

    /**
     * Invalidate calendar cache.
     */
    public function invalidateCalendar(): void
    {
        $this->calendarCache->invalidateTags(['calendar']);
        $this->log('Cache invalidated: calendar');
    }

    // ==================== PAYMENTS CACHING ====================

    /**
     * Get cached debtors report.
     */
    public function getDebtorsReport(?int $gradeId, callable $loader): array
    {
        $key = sprintf('debtors_%s', $gradeId ?? 'all');

        return $this->paymentsCache->get($key, function (ItemInterface $item) use ($loader, $gradeId) {
            $item->expiresAfter(self::TTL_SHORT);
            $item->tag(['payments', 'debtors']);

            $this->log('Cache MISS: debtors report', ['grade' => $gradeId]);

            return $loader();
        });
    }

    /**
     * Get cached daily closure.
     */
    public function getDailyClosure(string $date, callable $loader): array
    {
        $key = sprintf('daily_closure_%s', $date);

        return $this->paymentsCache->get($key, function (ItemInterface $item) use ($loader, $date) {
            // Today's closure should be short-lived, past days can be long
            $isToday = $date === date('Y-m-d');
            $item->expiresAfter($isToday ? self::TTL_SHORT : self::TTL_LONG);
            $item->tag(['payments', 'closure']);

            $this->log('Cache MISS: daily closure', ['date' => $date]);

            return $loader();
        });
    }

    /**
     * Get cached payment plan.
     */
    public function getPaymentPlan(string $planId, callable $loader): array
    {
        $key = sprintf('payment_plan_%s', $planId);

        return $this->paymentsCache->get($key, function (ItemInterface $item) use ($loader) {
            $item->expiresAfter(self::TTL_STANDARD);
            $item->tag(['payments', 'plans']);

            return $loader();
        });
    }

    /**
     * Invalidate payment caches.
     */
    public function invalidatePayments(): void
    {
        $this->paymentsCache->invalidateTags(['payments']);
        $this->log('Cache invalidated: payments');
    }

    /**
     * Invalidate specific payment plan.
     */
    public function invalidatePaymentPlan(string $planId): void
    {
        $this->paymentsCache->delete(sprintf('payment_plan_%s', $planId));
        // Also invalidate debtors since payment status affects it
        $this->paymentsCache->invalidateTags(['debtors']);
    }

    // ==================== UTILITY METHODS ====================

    /**
     * Clear all caches.
     */
    public function clearAll(): void
    {
        $this->gradesCache->invalidateTags(['grades']);
        $this->announcementsCache->invalidateTags(['announcements']);
        $this->calendarCache->invalidateTags(['calendar']);
        $this->paymentsCache->invalidateTags(['payments']);

        $this->log('All caches cleared');
    }

    /**
     * Get cache statistics (for monitoring).
     */
    public function getStats(): array
    {
        return [
            'grades' => 'active',
            'announcements' => 'active',
            'calendar' => 'active',
            'payments' => 'active',
        ];
    }

    /**
     * Log cache operations.
     */
    private function log(string $message, array $context = []): void
    {
        $this->logger?->debug('[Cache] ' . $message, $context);
    }
}
