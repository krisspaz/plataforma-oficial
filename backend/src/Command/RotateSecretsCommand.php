<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Security\SecretRotationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:security:rotate-secrets',
    description: 'Rota secretos de seguridad (JWT, APP_SECRET, API keys)',
)]
final class RotateSecretsCommand extends Command
{
    public function __construct(
        private readonly SecretRotationService $secretRotationService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Rotación de Secretos de Seguridad');

        if (!$this->secretRotationService->shouldRotate()) {
            $io->warning('No es necesario rotar secretos aún (última rotación hace menos de 30 días)');
            return Command::SUCCESS;
        }

        $io->section('Rotando claves JWT...');
        try {
            $jwtResult = $this->secretRotationService->rotateJWTKeys();
            $io->success('Claves JWT rotadas exitosamente');
            $io->writeln('Backup: ' . $jwtResult['backup_location']);
            $io->writeln('Próxima rotación: ' . $jwtResult['next_rotation']);
        } catch (\Exception $e) {
            $io->error('Error rotando claves JWT: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $io->section('Rotando APP_SECRET...');
        try {
            $this->secretRotationService->rotateAppSecret();
            $io->success('APP_SECRET rotado exitosamente');
        } catch (\Exception $e) {
            $io->error('Error rotando APP_SECRET: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $io->section('Rotando API keys externas...');
        try {
            $apiKeys = $this->secretRotationService->rotateExternalAPIKeys();
            $io->success('API keys rotadas: ' . implode(', ', array_keys($apiKeys)));
        } catch (\Exception $e) {
            $io->error('Error rotando API keys: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $io->success('Rotación de secretos completada exitosamente');
        $io->warning('IMPORTANTE: Reinicie la aplicación para aplicar los cambios');

        return Command::SUCCESS;
    }
}
