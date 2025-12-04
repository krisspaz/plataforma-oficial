<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251203_OptimizedMigration extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Migración optimizada con índices, particionamiento y constraints seguras para production';
    }

    public function up(Schema $schema): void
    {
        $this->connection->beginTransaction();

        // ───────────────────────────────
        // 1️⃣ Tabla enrollments particionada por academic_year
        $this->addSql('
            CREATE TABLE enrollments_new (
                LIKE enrollments INCLUDING ALL
            ) PARTITION BY RANGE (academic_year)
        ');

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

        // Índices específicos
        $this->addSql('CREATE INDEX idx_enrollments_student_year ON enrollments(student_id, academic_year)');
        $this->addSql('CREATE INDEX idx_enrollments_status_active ON enrollments(status) WHERE status = \'active\'');

        // ───────────────────────────────
        // 2️⃣ Tabla grades particionada por grading_period_id
        $this->addSql('
            CREATE TABLE grades_new (
                LIKE grades INCLUDING ALL
            ) PARTITION BY RANGE (grading_period_id)
        ');

        for ($i = 1; $i <= 20; $i++) {
            $this->addSql("
                CREATE TABLE grades_p{$i} PARTITION OF grades_new
                FOR VALUES FROM ({$i}) TO (" . ($i + 1) . ")
            ");
        }

        $this->addSql('INSERT INTO grades_new SELECT * FROM grades');
        $this->addSql('ALTER TABLE grades RENAME TO grades_old');
        $this->addSql('ALTER TABLE grades_new RENAME TO grades');

        // Índices
        $this->addSql('CREATE INDEX idx_grades_student_period ON grades(student_id, grading_period_id)');

        // ───────────────────────────────
        // 3️⃣ Tabla audit_logs particionada por fecha (mensual)
        $this->addSql('
            CREATE TABLE audit_logs_new (
                LIKE audit_logs INCLUDING ALL
            ) PARTITION BY RANGE (created_at)
        ');

        $date = new \DateTime();
        for ($i = 0; $i < 12; $i++) {
            $startDate = $date->format('Y-m-01');
            $date->modify('+1 month');
            $endDate = $date->format('Y-m-01');
            $partitionName = 'audit_logs_' . str_replace('-', '_', substr($startDate, 0, 7));

            $this->addSql("
                CREATE TABLE {$partitionName} PARTITION OF audit_logs_new
                FOR VALUES FROM ('{$startDate}') TO ('{$endDate}')
            ");
        }

        $this->addSql('INSERT INTO audit_logs_new SELECT * FROM audit_logs');
        $this->addSql('ALTER TABLE audit_logs RENAME TO audit_logs_old');
        $this->addSql('ALTER TABLE audit_logs_new RENAME TO audit_logs');

        // Índices
        $this->addSql('CREATE INDEX idx_audit_created_brin ON audit_logs USING brin(created_at)');
        $this->addSql('CREATE INDEX idx_audit_user ON audit_logs(user_id)');

        // ───────────────────────────────
        // 4️⃣ Índices optimizados para JSON, roles y campos frecuentes
        $this->addSql('CREATE INDEX idx_users_roles_gin ON users USING gin(roles)');
        $this->addSql('CREATE UNIQUE INDEX idx_users_email_lower ON users(LOWER(email))');

        $this->addSql('CREATE INDEX idx_payments_status_active ON payments(status) WHERE status=\'active\'');
        $this->addSql('CREATE INDEX idx_payments_due_date ON payments(due_date) WHERE status != \'paid\'');

        // ───────────────────────────────
        // 5️⃣ Constraints seguros con ON DELETE CASCADE y DEFERRABLE
        $this->addSql('ALTER TABLE enrollments ADD CONSTRAINT FK_enrollments_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED');
        $this->addSql('ALTER TABLE enrollments ADD CONSTRAINT FK_enrollments_grade FOREIGN KEY (grade_id) REFERENCES grades(id) DEFERRABLE INITIALLY DEFERRED');
        $this->addSql('ALTER TABLE enrollments ADD CONSTRAINT FK_enrollments_section FOREIGN KEY (section_id) REFERENCES sections(id) DEFERRABLE INITIALLY DEFERRED');

        $this->addSql('ALTER TABLE parent_student ADD CONSTRAINT FK_parent_student_parent FOREIGN KEY (parent_entity_id) REFERENCES parents(id) ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED');
        $this->addSql('ALTER TABLE parent_student ADD CONSTRAINT FK_parent_student_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE DEFERRABLE INITIALLY DEFERRED');

        $this->connection->commit();
    }

    public function down(Schema $schema): void
    {
        $this->connection->beginTransaction();

        // Restaurar tablas originales
        $this->addSql('DROP TABLE IF EXISTS enrollments CASCADE');
        $this->addSql('ALTER TABLE enrollments_old RENAME TO enrollments');

        $this->addSql('DROP TABLE IF EXISTS grades CASCADE');
        $this->addSql('ALTER TABLE grades_old RENAME TO grades');

        $this->addSql('DROP TABLE IF EXISTS audit_logs CASCADE');
        $this->addSql('ALTER TABLE audit_logs_old RENAME TO audit_logs');

        // Limpiar índices
        $this->addSql('DROP INDEX IF EXISTS idx_users_roles_gin');
        $this->addSql('DROP INDEX IF EXISTS idx_users_email_lower');
        $this->addSql('DROP INDEX IF EXISTS idx_payments_status_active');
        $this->addSql('DROP INDEX IF EXISTS idx_payments_due_date');

        $this->connection->commit();
    }
}
