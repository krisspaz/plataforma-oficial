<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251203_PartitionTablesOptimized extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Particiona enrollments, grades y audit_logs con tablas intermedias y validaciones para mayor performance';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('BEGIN');

        // -----------------------
        // Enrollments
        // -----------------------
        $this->addSql('CREATE TABLE enrollments_new (LIKE enrollments INCLUDING ALL) PARTITION BY RANGE (academic_year)');

        $currentYear = (int) date('Y');
        for ($year = $currentYear - 5; $year <= $currentYear + 2; $year++) {
            $this->addSql("
                CREATE TABLE enrollments_y{$year} PARTITION OF enrollments_new
                FOR VALUES FROM ({$year}) TO (" . ($year + 1) . ")
            ");
        }

        $this->addSql('INSERT INTO enrollments_new SELECT * FROM enrollments');
        $this->addSql('ALTER TABLE enrollments RENAME TO enrollments_old');
        $this->addSql('ALTER TABLE enrollments_new RENAME TO enrollments');

        // -----------------------
        // Grades
        // -----------------------
        $this->addSql('CREATE TABLE grades_new (LIKE grades INCLUDING ALL) PARTITION BY RANGE (grading_period_id)');

        $maxPeriod = $this->connection->fetchOne('SELECT MAX(grading_period_id) FROM grades');
        for ($i = 1; $i <= (int) $maxPeriod; $i++) {
            $this->addSql("
                CREATE TABLE grades_p{$i} PARTITION OF grades_new
                FOR VALUES FROM ({$i}) TO (" . ($i + 1) . ")
            ");
        }

        $this->addSql('INSERT INTO grades_new SELECT * FROM grades');
        $this->addSql('ALTER TABLE grades RENAME TO grades_old');
        $this->addSql('ALTER TABLE grades_new RENAME TO grades');

        // -----------------------
        // Audit Logs
        // -----------------------
        $this->addSql('CREATE TABLE audit_logs_new (LIKE audit_logs INCLUDING ALL) PARTITION BY RANGE (created_at)');

        $date = new \DateTime('first day of -11 months');
        for ($i = 0; $i < 12; $i++) {
            $startDate = $date->format('Y-m-d');
            $date->modify('+1 month');
            $endDate = $date->format('Y-m-d');
            $partitionName = 'audit_logs_' . str_replace('-', '_', substr($startDate, 0, 7));

            $this->addSql("
                CREATE TABLE {$partitionName} PARTITION OF audit_logs_new
                FOR VALUES FROM (TO_DATE('{$startDate}','YYYY-MM-DD')) TO (TO_DATE('{$endDate}','YYYY-MM-DD'))
            ");
        }

        $this->addSql('INSERT INTO audit_logs_new SELECT * FROM audit_logs');
        $this->addSql('ALTER TABLE audit_logs RENAME TO audit_logs_old');
        $this->addSql('ALTER TABLE audit_logs_new RENAME TO audit_logs');

        $this->addSql('COMMIT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('BEGIN');
        $this->addSql('DROP TABLE IF EXISTS enrollments CASCADE');
        $this->addSql('ALTER TABLE enrollments_old RENAME TO enrollments');

        $this->addSql('DROP TABLE IF EXISTS grades CASCADE');
        $this->addSql('ALTER TABLE grades_old RENAME TO grades');

        $this->addSql('DROP TABLE IF EXISTS audit_logs CASCADE');
        $this->addSql('ALTER TABLE audit_logs_old RENAME TO audit_logs');
        $this->addSql('COMMIT');
    }
}
