# Payment System Documentation

## Overview

The Payment System is a comprehensive module for managing student payments, installment plans, and financial reporting. It follows Domain-Driven Design (DDD) and CQRS architectural patterns.

## Architecture

### Domain Layer (`src/Domain/Payment`)

#### Value Objects
- **Amount**: Represents monetary values with currency support
  - Immutable
  - Supports arithmetic operations (add, subtract, multiply, divide)
  - Currency validation
  - Formatting utilities

- **PaymentMethod**: Enum-like value object for payment methods
  - Cash, Card, Transfer, Stripe, PayPal, BAC, Installments
  - Display name localization

- **InstallmentNumber**: Represents installment position in a plan
  - Number and total validation
  - Helper methods (isFirst, isLast, getRemaining)

- **DueDate**: Represents payment due dates with business logic
  - Overdue detection
  - Days until/overdue calculation
  - Overdue level classification (current, warning, danger, critical)

#### Entities
- **PaymentPlan**: Rich domain model for payment plans
  - Creates installments automatically
  - Calculates progress, totals paid/pending
  - Detects overdue installments
  - Lifecycle management (active, completed, cancelled)

- **Installment**: Individual payment within a plan
  - Payment recording
  - Overdue tracking
  - Receipt generation

#### Domain Services
- **PaymentPlanCalculator**: Business logic for payment calculations
  - Monthly payment calculation
  - Interest calculation
  - Optimal plan suggestion based on amount
  - Viability validation
  - Schedule date generation

- **DebtorReportGenerator**: Generates debtor reports
  - Comprehensive debtor analysis
  - Daily closure reports
  - Grouping and filtering

#### Repository Interfaces
- `PaymentPlanRepositoryInterface`
- `InstallmentRepositoryInterface`

### Application Layer (`src/Application/Payment`)

#### Commands (Write Operations)
- **CreatePaymentPlanCommand**: Create a new payment plan
- **RecordInstallmentPaymentCommand**: Record a payment for an installment

#### Queries (Read Operations)
- **GetDebtorsQuery**: Get debtor report with filters
- **GetDailyClosureQuery**: Get daily closure report

#### DTOs
- **PaymentPlanDTO**: Data transfer object for payment plans
- **InstallmentDTO**: Data transfer object for installments

### Infrastructure Layer (`src/Infrastructure/Payment`)

#### Repositories
- **DoctrinePaymentPlanRepository**: Doctrine implementation
- **DoctrineInstallmentRepository**: Doctrine implementation

### Presentation Layer

#### API Controller
- `POST /api/payments/plans` - Create payment plan
- `POST /api/payments/installments/{id}/pay` - Record payment
- `GET /api/payments/debtors` - Get debtor report
- `GET /api/payments/daily-closure` - Get daily closure
- `GET /api/payments/history/{studentId}` - Get payment history

## Frontend Components

### Services
- **paymentService**: API client for payment operations

### Components
- **PaymentPlanForm**: Form to create payment plans with preview
- **DailyClosureModal**: Modal for daily closure report
- **ReporteDeudores**: Comprehensive debtor report page

## Database Schema

### payment_plans
- `id` (UUID, PK)
- `enrollment_id` (FK to enrollments)
- `total_amount` (NUMERIC)
- `number_of_installments` (INT)
- `installment_amount` (NUMERIC)
- `day_of_month` (INT)
- `status` (VARCHAR)
- `created_at` (TIMESTAMP)
- `completed_at` (TIMESTAMP)
- `metadata` (JSONB)

### installments
- `id` (UUID, PK)
- `payment_plan_id` (FK to payment_plans)
- `number` (INT)
- `total_installments` (INT)
- `amount` (NUMERIC)
- `due_date` (DATE)
- `paid_at` (TIMESTAMP)
- `status` (VARCHAR)
- `receipt_number` (VARCHAR)
- `payment_method` (VARCHAR)
- `metadata` (JSONB)

## Business Rules

1. **Payment Plans**
   - Minimum 1, maximum 12 installments
   - Day of month must be between 1-28 (to avoid month-end issues)
   - Only one active plan per enrollment
   - Minimum monthly payment: Q50.00

2. **Installments**
   - Cannot pay an already paid installment
   - Receipt number auto-generated if not provided
   - Payment marks plan as complete when all installments paid

3. **Overdue Levels**
   - Current: 0 days
   - Warning: 1-15 days
   - Danger: 16-30 days
   - Critical: 31+ days

4. **Optimal Plan Suggestions**
   - ≤ Q1,000: 1 installment (cash)
   - Q1,001 - Q3,000: 3 installments
   - Q3,001 - Q6,000: 6 installments
   - > Q6,000: 10 installments

## Testing

### Unit Tests
- `AmountTest`: Value object operations
- `PaymentPlanTest`: Entity business logic
- `PaymentPlanCalculatorTest`: Calculation service
- Coverage target: >70%

### Integration Tests
- Repository operations
- Command/Query handlers
- API endpoints

## Usage Examples

### Creating a Payment Plan

```php
$command = new CreatePaymentPlanCommand(
    enrollmentId: 123,
    totalAmount: 3000.00,
    numberOfInstallments: 10,
    dayOfMonth: 5
);

$plan = $handler($command);
```

### Recording a Payment

```php
$command = new RecordInstallmentPaymentCommand(
    installmentId: 'uuid-here',
    paymentMethod: 'cash',
    receiptNumber: 'REC-001'
);

$installment = $handler($command);
```

### Getting Debtor Report

```php
$query = new GetDebtorsQuery(
    level: 'critical',
    minDaysOverdue: 30
);

$report = $handler($query);
```

## Security

- All endpoints require `ROLE_SECRETARIA` permission
- Audit logs created for all payment recordings
- Input validation on all commands
- SQL injection prevention via Doctrine ORM

## Performance Optimizations

- Database indexes on frequently queried fields
- Eager loading of relationships
- Query result caching for reports
- Pagination for large result sets

## Future Enhancements

1. Automated payment reminders (email/SMS)
2. Online payment integration
3. Payment plan renegotiation
4. Discount and scholarship management
5. Multi-currency support
6. Payment forecasting and analytics
