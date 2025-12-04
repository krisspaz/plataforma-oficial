<?php

namespace App\Tests\Unit\Service;

use App\Entity\AIRiskScore;
use App\Entity\Student;
use App\Repository\AIRiskScoreRepository;
use App\Service\AIRiskPredictionService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class AIRiskPredictionServiceTest extends TestCase
{
    private $riskScoreRepository;
    private $entityManager;
    private $service;

    protected function setUp(): void
    {
        $this->riskScoreRepository = $this->createMock(AIRiskScoreRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->service = new AIRiskPredictionService(
            $this->riskScoreRepository,
            $this->entityManager
        );
    }

    public function testCalculateRiskCreatesNewScore(): void
    {
        $student = $this->createMock(Student::class);
        $student->method('getId')->willReturn(1);
        $student->method('getEnrollments')->willReturn(new ArrayCollection());

        $this->riskScoreRepository->method('findLatestForStudent')
            ->with(1)
            ->willReturn(null);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(AIRiskScore::class));

        $this->entityManager->expects($this->once())
            ->method('flush');

        $result = $this->service->calculateRisk($student);

        $this->assertInstanceOf(AIRiskScore::class, $result);
    }

    public function testCalculateRiskUpdatesExistingScore(): void
    {
        $student = $this->createMock(Student::class);
        $student->method('getId')->willReturn(1);
        $student->method('getEnrollments')->willReturn(new ArrayCollection());

        $existingScore = new AIRiskScore();

        $this->riskScoreRepository->method('findLatestForStudent')
            ->with(1)
            ->willReturn($existingScore);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($existingScore);

        $result = $this->service->calculateRisk($student);

        $this->assertSame($existingScore, $result);
    }
}
