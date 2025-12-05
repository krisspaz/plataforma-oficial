# ADR-003: CQRS Pattern Implementation

## Status
Accepted

## Date
2024-12-05

## Context
The platform needed better separation of concerns, improved testability, and a path to eventual event sourcing. Controllers were becoming complex with mixed read/write logic.

## Decision
Implement the CQRS (Command Query Responsibility Segregation) pattern across all major controllers using Symfony Messenger component.

### Controllers Refactored:
- PaymentController
- EnrollmentController
- TeacherController
- ParentController
- ChatController
- ContractController
- AcademicController

### Structure:
```
src/Application/
├── Academic/Query/
├── Chat/{Command,Query}/
├── Contract/{Command,Query}/
├── Enrollment/{Command,Query}/
├── Parent/Query/
├── Payment/{Command,Query}/
└── Teacher/Query/
```

### Pattern:
```php
// Query
final class GetPaymentsQuery {
    public function __construct(
        public readonly ?string $status = null,
        public readonly int $page = 1,
        public readonly int $limit = 20
    ) {}
}

// Handler
#[AsMessageHandler]
final class GetPaymentsHandler {
    public function __invoke(GetPaymentsQuery $query): array {
        // Query logic
    }
}

// Controller
$result = $this->handleQuery(new GetPaymentsQuery($status));
```

## Consequences

### Positive
- Clear separation of read/write operations
- Improved testability (handlers are easy to unit test)
- Consistent patterns across codebase
- Ready for async processing via Messenger
- Cache implementation is straightforward

### Negative
- More files to maintain (~102 new files)
- Learning curve for new developers
- Slightly more boilerplate code

### Neutral
- Handlers auto-register via `#[AsMessageHandler]` attribute
- MessageBusInterface injection in controllers
