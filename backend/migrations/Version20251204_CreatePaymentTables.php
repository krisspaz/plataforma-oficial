<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration for Payment Plan and Installment tables
 */
final class Version20251204_CreatePaymentTables extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create payment_plans and installments tables for the payment system';
    }

    public function up(Schema $schema): void
    {
        // Create payment_plans table
        $this->addSql('
            CREATE TABLE payment_plans (
                id UUID PRIMARY KEY,
                enrollment_id INT NOT NULL,
                total_amount NUMERIC(10, 2) NOT NULL,
                number_of_installments INT NOT NULL,
                installment_amount NUMERIC(10, 2) NOT NULL,
                day_of_month INT NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT \'active\',
                created_at TIMESTAMP NOT NULL,
                completed_at TIMESTAMP DEFAULT NULL,
                metadata JSONB DEFAULT NULL,
                CONSTRAINT fk_payment_plan_enrollment FOREIGN KEY (enrollment_id) 
                    REFERENCES enrollments(id) ON DELETE CASCADE,
                CONSTRAINT chk_installments_range CHECK (number_of_installments BETWEEN 1 AND 12),
                CONSTRAINT chk_day_of_month CHECK (day_of_month BETWEEN 1 AND 28),
                CONSTRAINT chk_status CHECK (status IN (\'active\', \'completed\', \'cancelled\'))
            )
        ');

        // Create installments table
        $this->addSql('
            CREATE TABLE installments (
                id UUID PRIMARY KEY,
                payment_plan_id UUID NOT NULL,
                number INT NOT NULL,
                total_installments INT NOT NULL,
                amount NUMERIC(10, 2) NOT NULL,
                due_date DATE NOT NULL,
                paid_at TIMESTAMP DEFAULT NULL,
                status VARCHAR(20) NOT NULL DEFAULT \'pending\',
                receipt_number VARCHAR(100) DEFAULT NULL,
                payment_method VARCHAR(50) DEFAULT NULL,
                metadata JSONB DEFAULT NULL,
                CONSTRAINT fk_installment_payment_plan FOREIGN KEY (payment_plan_id) 
                    REFERENCES payment_plans(id) ON DELETE CASCADE,
                CONSTRAINT chk_installment_status CHECK (status IN (\'pending\', \'paid\', \'cancelled\'))
            )
        ');

        // Create indexes for performance
        $this->addSql('CREATE INDEX idx_payment_plans_enrollment ON payment_plans(enrollment_id)');
        $this->addSql('CREATE INDEX idx_payment_plans_status ON payment_plans(status)');
        $this->addSql('CREATE INDEX idx_installments_payment_plan ON installments(payment_plan_id)');
        $this->addSql('CREATE INDEX idx_installments_status ON installments(status)');
        $this->addSql('CREATE INDEX idx_installments_due_date ON installments(due_date)');
        $this->addSql('CREATE INDEX idx_installments_paid_at ON installments(paid_at)');

        // Index for finding overdue installments
        $this->addSql('CREATE INDEX idx_installments_overdue ON installments(status, due_date) WHERE status = \'pending\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS installments CASCADE');
        $this->addSql('DROP TABLE IF EXISTS payment_plans CASCADE');
    }
}
