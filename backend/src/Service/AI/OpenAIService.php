<?php

declare(strict_types=1);

namespace App\Service\AI;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

final class OpenAIService
{
    private const API_URL = 'https://api.openai.com/v1';

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private string $apiKey,
    ) {}

    /**
     * Genera respuesta del asistente educativo usando GPT-4
     */
    public function generateEducationalResponse(string $question, array $context = []): array
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'Eres un asistente educativo experto que ayuda a estudiantes, padres y maestros con consultas académicas. Proporciona respuestas claras, precisas y educativas.'
            ]
        ];

        // Agregar contexto si existe
        if (!empty($context)) {
            $messages[] = [
                'role' => 'system',
                'content' => 'Contexto: ' . json_encode($context)
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $question
        ];

        try {
            $response = $this->httpClient->request('POST', self::API_URL . '/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4-turbo-preview',
                    'messages' => $messages,
                    'temperature' => 0.7,
                    'max_tokens' => 1000,
                ]
            ]);

            $data = $response->toArray();

            return [
                'success' => true,
                'response' => $data['choices'][0]['message']['content'],
                'usage' => $data['usage'],
            ];
        } catch (\Exception $e) {
            $this->logger->error('OpenAI API error', [
                'error' => $e->getMessage(),
                'question' => $question
            ]);

            return [
                'success' => false,
                'error' => 'Error al generar respuesta',
            ];
        }
    }

    /**
     * Analiza el sentimiento de un texto (para detectar estudiantes con problemas)
     */
    public function analyzeSentiment(string $text): array
    {
        try {
            $response = $this->httpClient->request('POST', self::API_URL . '/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4-turbo-preview',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Analiza el sentimiento del siguiente texto y clasifícalo como: positivo, neutral, negativo, o preocupante. Si es preocupante, indica si requiere intervención inmediata.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $text
                        ]
                    ],
                    'temperature' => 0.3,
                ]
            ]);

            $data = $response->toArray();
            $analysis = $data['choices'][0]['message']['content'];

            return [
                'success' => true,
                'sentiment' => $this->extractSentiment($analysis),
                'requires_intervention' => str_contains(strtolower($analysis), 'intervención'),
                'analysis' => $analysis,
            ];
        } catch (\Exception $e) {
            $this->logger->error('Sentiment analysis error', ['error' => $e->getMessage()]);
            return ['success' => false];
        }
    }

    /**
     * Genera resumen de rendimiento académico
     */
    public function generateAcademicSummary(array $studentData): string
    {
        $prompt = sprintf(
            "Genera un resumen académico conciso para un estudiante con los siguientes datos:\n" .
                "Promedio: %.2f\n" .
                "Asistencia: %d%%\n" .
                "Materias reprobadas: %d\n" .
                "Comportamiento: %s\n" .
                "Proporciona recomendaciones específicas.",
            $studentData['average'] ?? 0,
            $studentData['attendance'] ?? 0,
            $studentData['failed_subjects'] ?? 0,
            $studentData['behavior'] ?? 'Normal'
        );

        try {
            $response = $this->httpClient->request('POST', self::API_URL . '/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4-turbo-preview',
                    'messages' => [
                        ['role' => 'system', 'content' => 'Eres un asesor académico experto.'],
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 500,
                ]
            ]);

            $data = $response->toArray();
            return $data['choices'][0]['message']['content'];
        } catch (\Exception $e) {
            $this->logger->error('Academic summary error', ['error' => $e->getMessage()]);
            return 'Error al generar resumen académico.';
        }
    }

    private function extractSentiment(string $analysis): string
    {
        $analysis = strtolower($analysis);

        if (str_contains($analysis, 'preocupante')) return 'concerning';
        if (str_contains($analysis, 'negativo')) return 'negative';
        if (str_contains($analysis, 'positivo')) return 'positive';

        return 'neutral';
    }
}
