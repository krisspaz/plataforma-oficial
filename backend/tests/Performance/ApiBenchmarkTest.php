<?php

declare(strict_types=1);

namespace App\Tests\Performance;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Performance benchmarks for critical API endpoints.
 * 
 * Run with: vendor/bin/phpunit tests/Performance --testsuite=performance
 */
class ApiBenchmarkTest extends WebTestCase
{
    private const ACCEPTABLE_RESPONSE_TIME_MS = 200;
    private const STRESS_ITERATIONS = 100;

    public function testPaymentPlanCreationPerformance(): void
    {
        $this->markTestSkipped('Performance tests require authenticated session');

        $client = static::createClient();
        $start = microtime(true);

        $client->request('POST', '/api/payments/plans', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'enrollment_id' => 1,
            'total_amount' => 1500,
            'num_installments' => 10
        ]));

        $elapsed = (microtime(true) - $start) * 1000;

        $this->assertLessThan(self::ACCEPTABLE_RESPONSE_TIME_MS, $elapsed);
    }

    public function testDebtorReportPerformance(): void
    {
        $this->markTestSkipped('Performance tests require authenticated session');

        $client = static::createClient();
        $start = microtime(true);

        $client->request('GET', '/api/payments/debtors');

        $elapsed = (microtime(true) - $start) * 1000;

        $this->assertLessThan(self::ACCEPTABLE_RESPONSE_TIME_MS * 2, $elapsed);
    }

    public function testGradeRecordingPerformance(): void
    {
        $this->markTestSkipped('Performance tests require authenticated session');

        $client = static::createClient();
        $start = microtime(true);

        $client->request('POST', '/api/grades', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'student_id' => 1,
            'subject_id' => 1,
            'teacher_id' => 1,
            'bimester' => 1,
            'grade' => 85
        ]));

        $elapsed = (microtime(true) - $start) * 1000;

        $this->assertLessThan(self::ACCEPTABLE_RESPONSE_TIME_MS, $elapsed);
    }

    public function testStudentGradesQueryPerformance(): void
    {
        $this->markTestSkipped('Performance tests require authenticated session');

        $client = static::createClient();
        $start = microtime(true);

        $client->request('GET', '/api/grades/student/1');

        $elapsed = (microtime(true) - $start) * 1000;

        $this->assertLessThan(self::ACCEPTABLE_RESPONSE_TIME_MS, $elapsed);
    }

    public function testAnnouncementsQueryPerformance(): void
    {
        $this->markTestSkipped('Performance tests require authenticated session');

        $client = static::createClient();
        $start = microtime(true);

        $client->request('GET', '/api/coordination/announcements');

        $elapsed = (microtime(true) - $start) * 1000;

        $this->assertLessThan(self::ACCEPTABLE_RESPONSE_TIME_MS, $elapsed);
    }

    public function testCalendarEventsQueryPerformance(): void
    {
        $this->markTestSkipped('Performance tests require authenticated session');

        $client = static::createClient();
        $start = microtime(true);

        $client->request('GET', '/api/coordination/calendar?start_date=2025-01-01&end_date=2025-12-31');

        $elapsed = (microtime(true) - $start) * 1000;

        $this->assertLessThan(self::ACCEPTABLE_RESPONSE_TIME_MS, $elapsed);
    }
}
