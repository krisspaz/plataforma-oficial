<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration for Coordination module tables
 */
final class Version20251204_CreateCoordinationTables extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create assignments, announcements, and calendar_events tables';
    }

    public function up(Schema $schema): void
    {
        // Create assignments table
        $this->addSql('
            CREATE TABLE assignments (
                id UUID PRIMARY KEY,
                teacher_id INT NOT NULL,
                subject_id INT NOT NULL,
                grade_id INT NOT NULL,
                section_id INT NOT NULL,
                academic_year INT NOT NULL,
                is_active BOOLEAN NOT NULL DEFAULT TRUE,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                CONSTRAINT fk_assignment_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id),
                CONSTRAINT fk_assignment_subject FOREIGN KEY (subject_id) REFERENCES subjects(id),
                CONSTRAINT fk_assignment_grade FOREIGN KEY (grade_id) REFERENCES grades(id),
                CONSTRAINT fk_assignment_section FOREIGN KEY (section_id) REFERENCES sections(id)
            )
        ');
        $this->addSql('COMMENT ON COLUMN assignments.created_at IS \'(DC2Type:datetime_immutable)\'');

        // Create announcements table
        $this->addSql('
            CREATE TABLE announcements (
                id UUID PRIMARY KEY,
                author_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                type VARCHAR(50) NOT NULL,
                target_ids JSON DEFAULT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                is_active BOOLEAN NOT NULL DEFAULT TRUE,
                CONSTRAINT fk_announcement_author FOREIGN KEY (author_id) REFERENCES users(id)
            )
        ');
        $this->addSql('COMMENT ON COLUMN announcements.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN announcements.expires_at IS \'(DC2Type:datetime_immutable)\'');

        // Create calendar_events table
        $this->addSql('
            CREATE TABLE calendar_events (
                id UUID PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT DEFAULT NULL,
                start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                end_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                type VARCHAR(50) NOT NULL,
                is_all_day BOOLEAN NOT NULL,
                academic_year INT NOT NULL
            )
        ');
        $this->addSql('COMMENT ON COLUMN calendar_events.start_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN calendar_events.end_date IS \'(DC2Type:datetime_immutable)\'');

        // Indexes
        $this->addSql('CREATE INDEX idx_assignments_teacher ON assignments(teacher_id)');
        $this->addSql('CREATE INDEX idx_assignments_grade_section ON assignments(grade_id, section_id)');
        $this->addSql('CREATE INDEX idx_announcements_active ON announcements(is_active, expires_at)');
        $this->addSql('CREATE INDEX idx_calendar_dates ON calendar_events(start_date, end_date)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS calendar_events');
        $this->addSql('DROP TABLE IF EXISTS announcements');
        $this->addSql('DROP TABLE IF EXISTS assignments');
    }
}
