<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251203_OptimizeIndexes extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Optimiza índices para mejorar performance de queries frecuentes con seguridad y sin bloquear tablas';
    }

    public function up(Schema $schema): void
    {
        // Students
        $this->addSql("CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_students_search ON students USING gin(to_tsvector('spanish', first_name || ' ' || last_name))");
        $this->addSql("CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_students_active ON students(status) WHERE status = 'active'");
        $this->addSql("CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_students_grade_section ON students(grade_id, section_id)");

        // Enrollments
        $this->addSql("CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_enrollments_academic_year ON enrollments(academic_year)");
        $this->addSql("CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_enrollments_student_year ON enrollments(student_id, academic_year)");
        $this->addSql("CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_enrollments_active ON enrollments(status) WHERE status = 'active'");

        // Payments
        $this->addSql("CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_payments_status ON payments(status)");
        $this->addSql("CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_payments_due_date ON payments(due_date) WHERE status != 'paid'");
        $this->addSql("CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_payments_student_status ON payments(student_id, status)");
        $this->addSql("CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_payments_created_at ON payments(created_at DESC)");

        // Grades
        $this->addSql("CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_grades_student_subject ON grades(student_id, subject_id)");
        $this->addSql("CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_grades_period ON grades(grading_period_id)");
        $this->addSql("CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_grades_student_period ON grades(student_id, grading_period_id)");

        // Attendance
        $this->addSql("CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_attendance_date ON attendance(attendance_date DESC)");
        $this->addSql("CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_attendance_student_date ON attendance(student_id, attendance_date)");
        $this->addSql("CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_attendance_status ON attendance(status)");

        // Audit logs
        $this->addSql("CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_audit_severity ON audit_logs(severity) WHERE severity IN ('warning','critical')");
        $this->addSql("CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_audit_user_action ON audit_logs(user_id, action)");
        $this->addSql("CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_audit_entity ON audit_logs(entity_type, entity_id)");

        // Users
        $this->addSql("CREATE UNIQUE INDEX CONCURRENTLY IF NOT EXISTS idx_users_email_lower ON users(LOWER(email))");
        $this->addSql("CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_users_roles ON users USING gin(roles)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IF EXISTS idx_students_search');
        $this->addSql('DROP INDEX IF EXISTS idx_students_active');
        $this->addSql('DROP INDEX IF EXISTS idx_students_grade_section');
        $this->addSql('DROP INDEX IF EXISTS idx_enrollments_academic_year');
        $this->addSql('DROP INDEX IF EXISTS idx_enrollments_student_year');
        $this->addSql('DROP INDEX IF EXISTS idx_enrollments_active');
        $this->addSql('DROP INDEX IF EXISTS idx_payments_status');
        $this->addSql('DROP INDEX IF EXISTS idx_payments_due_date');
        $this->addSql('DROP INDEX IF EXISTS idx_payments_student_status');
        $this->addSql('DROP INDEX IF EXISTS idx_payments_created_at');
        $this->addSql('DROP INDEX IF EXISTS idx_grades_student_subject');
        $this->addSql('DROP INDEX IF EXISTS idx_grades_period');
        $this->addSql('DROP INDEX IF EXISTS idx_grades_student_period');
        $this->addSql('DROP INDEX IF EXISTS idx_attendance_date');
        $this->addSql('DROP INDEX IF EXISTS idx_attendance_student_date');
        $this->addSql('DROP INDEX IF EXISTS idx_attendance_status');
        $this->addSql('DROP INDEX IF EXISTS idx_audit_severity');
        $this->addSql('DROP INDEX IF EXISTS idx_audit_user_action');
        $this->addSql('DROP INDEX IF EXISTS idx_audit_entity');
        $this->addSql('DROP INDEX IF EXISTS idx_users_email_lower');
        $this->addSql('DROP INDEX IF EXISTS idx_users_roles');
    }
}
