<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Infrastructure\Cache\CacheService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/cache')]
#[IsGranted('ROLE_ADMIN')]
class CacheController extends AbstractController
{
    public function __construct(
        private readonly CacheService $cache
    ) {}

    /**
     * Get cache statistics.
     */
    #[Route('/stats', name: 'api_cache_stats', methods: ['GET'])]
    public function stats(): JsonResponse
    {
        return $this->json([
            'success' => true,
            'data' => $this->cache->getStats()
        ]);
    }

    /**
     * Clear all caches.
     */
    #[Route('/clear', name: 'api_cache_clear', methods: ['POST'])]
    public function clearAll(): JsonResponse
    {
        try {
            $this->cache->clearAll();

            return $this->json([
                'success' => true,
                'message' => 'All caches cleared'
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to clear cache'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Clear grades cache.
     */
    #[Route('/clear/grades', name: 'api_cache_clear_grades', methods: ['POST'])]
    public function clearGrades(): JsonResponse
    {
        $this->cache->invalidateAllGrades();

        return $this->json([
            'success' => true,
            'message' => 'Grades cache cleared'
        ]);
    }

    /**
     * Clear announcements cache.
     */
    #[Route('/clear/announcements', name: 'api_cache_clear_announcements', methods: ['POST'])]
    public function clearAnnouncements(): JsonResponse
    {
        $this->cache->invalidateAnnouncements();

        return $this->json([
            'success' => true,
            'message' => 'Announcements cache cleared'
        ]);
    }

    /**
     * Clear calendar cache.
     */
    #[Route('/clear/calendar', name: 'api_cache_clear_calendar', methods: ['POST'])]
    public function clearCalendar(): JsonResponse
    {
        $this->cache->invalidateCalendar();

        return $this->json([
            'success' => true,
            'message' => 'Calendar cache cleared'
        ]);
    }

    /**
     * Clear payments cache.
     */
    #[Route('/clear/payments', name: 'api_cache_clear_payments', methods: ['POST'])]
    public function clearPayments(): JsonResponse
    {
        $this->cache->invalidatePayments();

        return $this->json([
            'success' => true,
            'message' => 'Payments cache cleared'
        ]);
    }
}
