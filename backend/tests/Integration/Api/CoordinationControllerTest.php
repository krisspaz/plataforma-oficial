<?php

declare(strict_types=1);

namespace App\Tests\Integration\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CoordinationControllerTest extends WebTestCase
{
    public function testCreateAssignmentRequiresAuth(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/coordination/assignments', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'teacher_id' => 1,
            'subject_id' => 1,
            'grade_id' => 1,
            'section_id' => 1
        ]));

        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetTeacherAssignmentsRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/coordination/assignments/teacher/1');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCreateAnnouncementRequiresAuth(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/coordination/announcements', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'title' => 'Test',
            'content' => 'Test content',
            'type' => 'general'
        ]));

        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetAnnouncementsRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/coordination/announcements');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCreateCalendarEventRequiresAuth(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/coordination/calendar', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'title' => 'Test Event',
            'start_date' => '2025-01-01',
            'end_date' => '2025-01-01',
            'type' => 'holiday'
        ]));

        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetCalendarEventsRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/coordination/calendar?start_date=2025-01-01&end_date=2025-01-31');

        $this->assertResponseStatusCodeSame(401);
    }
}
