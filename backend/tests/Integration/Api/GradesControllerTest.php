<?php

declare(strict_types=1);

namespace App\Tests\Integration\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GradesControllerTest extends WebTestCase
{
    public function testRecordGradeRequiresAuth(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/grades', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'student_id' => 1,
            'subject_id' => 1,
            'teacher_id' => 1,
            'bimester' => 1,
            'grade' => 85.0
        ]));

        $this->assertResponseStatusCodeSame(401);
    }

    public function testGetStudentGradesRequiresAuth(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/grades/student/1');

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCloseBimesterRequiresAuth(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/grades/bimester/close', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'grade_id' => 1,
            'bimester' => 1
        ]));

        $this->assertResponseStatusCodeSame(401);
    }

    public function testBulkRecordGradesRequiresAuth(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/grades/bulk', [], [], [
            'CONTENT_TYPE' => 'application/json'
        ], json_encode([
            'teacher_id' => 1,
            'subject_id' => 1,
            'bimester' => 1,
            'grades' => [
                ['student_id' => 1, 'grade' => 80],
                ['student_id' => 2, 'grade' => 85]
            ]
        ]));

        $this->assertResponseStatusCodeSame(401);
    }
}
