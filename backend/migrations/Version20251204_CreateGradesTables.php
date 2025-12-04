<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration for Grades module tables
 */
final class Version20251204_CreateGradesTables extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create grade_records and bimester_closures tables';
    }

    public function up(Schema $schema): void
    {
        // Create grade_records table
        $this->addSql('
            CREATE TABLE grade_records (
                id UUID PRIMARY KEY,
                student_id INT NOT NULL,
                subject_id INT NOT NULL,
                recorded_by_id INT NOT NULL,
                bimester INT NOT NULL,
                academic_year INT NOT NULL,
                grade DECIMAL(5,2) NOT NULL,
                comments TEXT DEFAULT NULL,
                recorded_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                CONSTRAINT fk_grade_student FOREIGN KEY (student_id) REFERENCES students(id),
                CONSTRAINT fk_grade_subject FOREIGN KEY (subject_id) REFERENCES subjects(id),
                CONSTRAINT fk_grade_teacher FOREIGN KEY (recorded_by_id) REFERENCES teachers(id),
                CONSTRAINT unique_grade_record UNIQUE (student_id, subject_id, bimester, academic_year)
            )
        ');
        $this->addSql('COMMENT ON COLUMN grade_records.recorded_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN grade_records.updated_at IS \'(DC2Type:datetime_immutable)\'');

        // Create bimester_closures table
        $this->addSql('
            CREATE TABLE bimester_closures (
                id UUID PRIMARY KEY,
                grade_id INT NOT NULL,
                bimester INT NOT NULL,
                academic_year INT NOT NULL,
                is_closed BOOLEAN NOT NULL DEFAULT FALSE,
                closed_by_id INT DEFAULT NULL,
                closed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                reopened_by_id INT DEFAULT NULL,
                reopened_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                reopen_reason TEXT DEFAULT NULL,
                CONSTRAINT fk_closure_grade FOREIGN KEY (grade_id) REFERENCES grades(id),
                CONSTRAINT fk_closure_closed_by FOREIGN KEY (closed_by_id) REFERENCES users(id),
                CONSTRAINT fk_closure_reopened_by FOREIGN KEY (reopened_by_id) REFERENCES users(id),
                CONSTRAINT unique_closure UNIQUE (grade_id, bimester, academic_year)
            )
        ');
        $this->addSql('COMMENT ON COLUMN bimester_closures.closed_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN bimester_closures.reopened_at IS \'(DC2Type:datetime_immutable)\'');

        // Indexes
        $this->addSql('CREATE INDEX idx_grades_student ON grade_records(student_id)');
        $this->addSql('CREATE INDEX idx_grades_subject_bimester ON grade_records(subject_id, bimester)');
        $this->addSql('CREATE INDEX idx_grades_academic_year ON grade_records(academic_year)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS bimester_closures');
        $this->addSql('DROP TABLE IF EXISTS grade_records');
    }
}
