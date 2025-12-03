<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251203_PartitionTables extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Implementa particionamiento de tablas por año académico para mejor performance';
    }

    public function up(Schema $schema): void
    {
        // Particionamiento de enrollments por año académico
        $this->addSql('
            CREATE TABLE enrollments_partitioned (
                LIKE enrollments INCLUDING ALL
            ) PARTITION BY RANGE (academic_year)
        ');

        // Crear particiones para los últimos 5 años y próximos 2
        $currentYear = (int) date('Y');
        for ($year = $currentYear - 5; $year <= $currentYear + 2; $year++) {
            $this->addSql("
                CREATE TABLE enrollments_y{$year} PARTITION OF enrollments_partitioned
                FOR VALUES FROM ({$year}) TO (" . ($year + 1) . ")
            ");
        }

        // Migrar datos existentes
        $this->addSql('INSERT INTO enrollments_partitioned SELECT * FROM enrollments');

        // Renombrar tablas
        $this->addSql('ALTER TABLE enrollments RENAME TO enrollments_old');
        $this->addSql('ALTER TABLE enrollments_partitioned RENAME TO enrollments');

        // Particionamiento de grades por período de calificación
        $this->addSql('
            CREATE TABLE grades_partitioned (
                LIKE grades INCLUDING ALL
            ) PARTITION BY RANGE (grading_period_id)
        ');

        // Crear particiones para períodos (asumiendo 4 períodos por año)
        for ($i = 1; $i <= 20; $i++) {
            $this->addSql("
                CREATE TABLE grades_p{$i} PARTITION OF grades_partitioned
                FOR VALUES FROM ({$i}) TO (" . ($i + 1) . ")
            ");
        }

        $this->addSql('INSERT INTO grades_partitioned SELECT * FROM grades');
        $this->addSql('ALTER TABLE grades RENAME TO grades_old');
        $this->addSql('ALTER TABLE grades_partitioned RENAME TO grades');

        // Particionamiento de audit_logs por fecha (mensual)
        $this->addSql('
            CREATE TABLE audit_logs_partitioned (
                LIKE audit_logs INCLUDING ALL
            ) PARTITION BY RANGE (created_at)
        ');

        // Crear particiones para los últimos 12 meses
        $date = new \DateTime();
        for ($i = 0; $i < 12; $i++) {
            $startDate = $date->format('Y-m-01');
            $date->modify('+1 month');
            $endDate = $date->format('Y-m-01');

            $partitionName = 'audit_logs_' . str_replace('-', '_', substr($startDate, 0, 7));

            $this->addSql("
                CREATE TABLE {$partitionName} PARTITION OF audit_logs_partitioned
                FOR VALUES FROM ('{$startDate}') TO ('{$endDate}')
            ");
        }

        $this->addSql('INSERT INTO audit_logs_partitioned SELECT * FROM audit_logs');
        $this->addSql('ALTER TABLE audit_logs RENAME TO audit_logs_old');
        $this->addSql('ALTER TABLE audit_logs_partitioned RENAME TO audit_logs');
    }

    public function down(Schema $schema): void
    {
        // Restaurar tablas originales
        $this->addSql('DROP TABLE IF EXISTS enrollments CASCADE');
        $this->addSql('ALTER TABLE enrollments_old RENAME TO enrollments');

        $this->addSql('DROP TABLE IF EXISTS grades CASCADE');
        $this->addSql('ALTER TABLE grades_old RENAME TO grades');

        $this->addSql('DROP TABLE IF EXISTS audit_logs CASCADE');
        $this->addSql('ALTER TABLE audit_logs_old RENAME TO audit_logs');
    }
}
