<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Psr\Log\LoggerInterface;

#[Route('/api/health')]
#[OA\Tag(name: 'Health Check')]
class HealthController extends AbstractController
{
    public function __construct(
        private readonly Connection $connection,
        private readonly LoggerInterface $logger
    ) {}

    #[Route('', name: 'api_health_check', methods: ['GET'])]
    #[OA\Get(
        path: '/api/health',
        summary: 'Check system health',
        description: 'Verifies database connection and application status',
        responses: [
            new OA\Response(
                response: 200,
                description: 'System is healthy',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok'),
                        new OA\Property(property: 'database', type: 'string', example: 'connected'),
                        new OA\Property(property: 'timestamp', type: 'string', format: 'date-time'),
                        new OA\Property(property: 'environment', type: 'string', example: 'prod')
                    ]
                )
            ),
            new OA\Response(response: 503, description: 'System is unhealthy')
        ]
    )]
    public function check(): JsonResponse
    {
        $status = 'ok';
        $dbStatus = 'connected';
        $httpCode = 200;

        try {
            $this->connection->executeQuery('SELECT 1');
        } catch (\Exception $e) {
            $status = 'error';
            $dbStatus = 'disconnected';
            $httpCode = 503;
            
            $this->logger->critical('Health check failed: Database connection error', [
                'error' => $e->getMessage()
            ]);
        }

        return $this->json([
            'status' => $status,
            'database' => $dbStatus,
            'timestamp' => (new \DateTime())->format('c'),
            'environment' => $this->getParameter('kernel.environment'),
            'version' => '1.0.0'
        ], $httpCode);
    }
}
