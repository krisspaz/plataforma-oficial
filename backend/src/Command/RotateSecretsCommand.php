<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Security\SecretRotationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:security:rotate-secrets',
    description: 'Rota secretos de seguridad (JWT, APP_SECRET, API keys)',
)]
final class RotateSecretsCommand extends Command
{
    private const ROTATION_DAYS_THRESHOLD = 30;

    public function __construct(
        private readonly SecretRotationService $secretRotationService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Forzar rotación sin verificar el tiempo transcurrido'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Simular rotación sin aplicar cambios'
            )
            ->addOption(
                'only',
                'o',
                InputOption::VALUE_REQUIRED,
                'Rotar solo un tipo específico: jwt, app-secret, api-keys'
            )
            ->setHelp(
                <<<'HELP'
                Este comando rota automáticamente los secretos de seguridad de la aplicación.

                Ejemplos de uso:
                  <info>php bin/console app:security:rotate-secrets</info>
                  <info>php bin/console app:security:rotate-secrets --force</info>
                  <info>php bin/console app:security:rotate-secrets --dry-run</info>
                  <info>php bin/console app:security:rotate-secrets --only=jwt</info>
                HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $force = $input->getOption('force');
        $dryRun = $input->getOption('dry-run');
        $only = $input->getOption('only');

        $io->title('Rotación de Secretos de Seguridad');

        if ($dryRun) {
            $io->note('Modo simulación - No se aplicarán cambios reales');
        }

        // Validar opción 'only'
        if ($only && !$this->isValidRotationType($only)) {
            $io->error(sprintf(
                'Tipo de rotación inválido: "%s". Valores permitidos: jwt, app-secret, api-keys',
                $only
            ));
            return Command::INVALID;
        }

        // Verificar si es necesario rotar
        if (!$force && !$this->secretRotationService->shouldRotate()) {
            $lastRotation = $this->secretRotationService->getLastRotationDate();
            $daysRemaining = $this->secretRotationService->getDaysUntilNextRotation();
            
            $io->warning([
                sprintf('No es necesario rotar secretos aún (última rotación: %s)', $lastRotation?->format('Y-m-d H:i:s') ?? 'nunca'),
                sprintf('Días restantes para próxima rotación: %d', $daysRemaining),
                'Use --force para forzar la rotación'
            ]);
            
            return Command::SUCCESS;
        }

        $io->writeln(sprintf(
            '<comment>Iniciando rotación%s...</comment>',
            $force ? ' (forzada)' : ''
        ));
        $io->newLine();

        $hasErrors = false;
        $rotatedSecrets = [];

        // Rotar claves JWT
        if ($this->shouldRotateType($only, 'jwt')) {
            $hasErrors = !$this->rotateJWTKeys($io, $dryRun, $rotatedSecrets) || $hasErrors;
        }

        // Rotar APP_SECRET
        if ($this->shouldRotateType($only, 'app-secret')) {
            $hasErrors = !$this->rotateAppSecret($io, $dryRun, $rotatedSecrets) || $hasErrors;
        }

        // Rotar API keys
        if ($this->shouldRotateType($only, 'api-keys')) {
            $hasErrors = !$this->rotateAPIKeys($io, $dryRun, $rotatedSecrets) || $hasErrors;
        }

        return $this->displaySummary($io, $hasErrors, $rotatedSecrets, $dryRun);
    }

    private function rotateJWTKeys(
        SymfonyStyle $io,
        bool $dryRun,
        array &$rotatedSecrets
    ): bool {
        $io->section('🔑 Rotando claves JWT');

        try {
            if ($dryRun) {
                $io->text('Simulando rotación de claves JWT...');
                $rotatedSecrets['jwt'] = ['status' => 'simulated'];
                $io->success('✓ Claves JWT simuladas exitosamente');
                return true;
            }

            $jwtResult = $this->secretRotationService->rotateJWTKeys();
            
            $io->success('✓ Claves JWT rotadas exitosamente');
            $io->definitionList(
                ['Backup guardado en' => $jwtResult['backup_location']],
                ['Claves generadas' => implode(', ', $jwtResult['keys'] ?? ['privada', 'pública'])],
                ['Próxima rotación' => $jwtResult['next_rotation']]
            );

            $rotatedSecrets['jwt'] = $jwtResult;
            return true;

        } catch (\Exception $e) {
            $io->error('✗ Error rotando claves JWT: ' . $e->getMessage());
            if ($io->isVerbose()) {
                $io->block($e->getTraceAsString(), null, 'fg=red');
            }
            return false;
        }
    }

    private function rotateAppSecret(
        SymfonyStyle $io,
        bool $dryRun,
        array &$rotatedSecrets
    ): bool {
        $io->section('🔐 Rotando APP_SECRET');

        try {
            if ($dryRun) {
                $io->text('Simulando rotación de APP_SECRET...');
                $rotatedSecrets['app_secret'] = ['status' => 'simulated'];
                $io->success('✓ APP_SECRET simulado exitosamente');
                return true;
            }

            $result = $this->secretRotationService->rotateAppSecret();
            
            $io->success('✓ APP_SECRET rotado exitosamente');
            $io->definitionList(
                ['Archivo actualizado' => $result['env_file'] ?? '.env.local'],
                ['Valor anterior' => substr($result['old_secret'] ?? '', 0, 10) . '...'],
                ['Nuevo valor' => substr($result['new_secret'] ?? '', 0, 10) . '...']
            );

            $rotatedSecrets['app_secret'] = $result;
            return true;

        } catch (\Exception $e) {
            $io->error('✗ Error rotando APP_SECRET: ' . $e->getMessage());
            if ($io->isVerbose()) {
                $io->block($e->getTraceAsString(), null, 'fg=red');
            }
            return false;
        }
    }

    private function rotateAPIKeys(
        SymfonyStyle $io,
        bool $dryRun,
        array &$rotatedSecrets
    ): bool {
        $io->section('🌐 Rotando API keys externas');

        try {
            if ($dryRun) {
                $io->text('Simulando rotación de API keys...');
                $rotatedSecrets['api_keys'] = ['status' => 'simulated'];
                $io->success('✓ API keys simuladas exitosamente');
                return true;
            }

            $apiKeys = $this->secretRotationService->rotateExternalAPIKeys();
            
            if (empty($apiKeys)) {
                $io->note('No hay API keys configuradas para rotar');
                return true;
            }

            $io->success(sprintf('✓ %d API keys rotadas exitosamente', count($apiKeys)));
            
            $rows = [];
            foreach ($apiKeys as $service => $data) {
                $rows[] = [
                    $service,
                    $data['rotated'] ? '✓' : '✗',
                    $data['message'] ?? 'OK'
                ];
            }
            
            $io->table(['Servicio', 'Estado', 'Mensaje'], $rows);

            $rotatedSecrets['api_keys'] = $apiKeys;
            return true;

        } catch (\Exception $e) {
            $io->error('✗ Error rotando API keys: ' . $e->getMessage());
            if ($io->isVerbose()) {
                $io->block($e->getTraceAsString(), null, 'fg=red');
            }
            return false;
        }
    }

    private function displaySummary(
        SymfonyStyle $io,
        bool $hasErrors,
        array $rotatedSecrets,
        bool $dryRun
    ): int {
        $io->newLine(2);
        $io->section('📊 Resumen de Rotación');

        $io->definitionList(
            ['Secretos procesados' => count($rotatedSecrets)],
            ['Estado' => $hasErrors ? '<error>Con errores</error>' : '<info>Exitoso</info>'],
            ['Modo' => $dryRun ? 'Simulación' : 'Producción']
        );

        if ($hasErrors) {
            $io->error([
                'La rotación se completó con errores.',
                'Revise los mensajes anteriores para más detalles.'
            ]);
            return Command::FAILURE;
        }

        if ($dryRun) {
            $io->success('Simulación completada exitosamente');
            $io->note('Ejecute sin --dry-run para aplicar los cambios reales');
            return Command::SUCCESS;
        }

        $io->success('🎉 Rotación de secretos completada exitosamente');
        
        $io->warning([
            '⚠️  ACCIÓN REQUERIDA:',
            '1. Reinicie la aplicación para aplicar los cambios',
            '2. Invalide las sesiones activas si es necesario',
            '3. Notifique a los servicios externos sobre las nuevas API keys',
            '4. Verifique los logs de la aplicación tras el reinicio'
        ]);

        return Command::SUCCESS;
    }

    private function isValidRotationType(?string $type): bool
    {
        if ($type === null) {
            return true;
        }

        return in_array($type, ['jwt', 'app-secret', 'api-keys'], true);
    }

    private function shouldRotateType(?string $only, string $type): bool
    {
        return $only === null || $only === $type;
    }
}