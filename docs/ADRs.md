# ADR-001: Hexagonal Architecture

## Status
Accepted

## Context
We need an architecture that:
- Separates business logic from infrastructure
- Enables easy testing
- Allows swapping implementations

## Decision
Adopt Hexagonal Architecture (Ports and Adapters) with:
- Domain layer at the center
- Application layer for use cases
- Infrastructure layer for external integrations

## Consequences
- Clear separation of concerns
- Higher initial complexity
- Excellent testability

---

# ADR-002: CQRS Pattern

## Status
Accepted

## Context
The application has distinct read and write patterns.

## Decision
Implement CQRS with Symfony Messenger:
- Commands for write operations
- Queries for read operations
- Separate handlers for each

## Consequences
- Clear separation of read/write logic
- Easier optimization of each path
- More boilerplate code

---

# ADR-003: Value Objects for Domain Concepts

## Status
Accepted

## Context
Need to ensure data integrity at the domain level.

## Decision
Use immutable Value Objects for:
- Amount (monetary values)
- PaymentMethod
- SignatureData
- ContractTemplate

## Consequences
- Guaranteed data validity
- Self-documenting code
- Slightly more verbose

---

# ADR-004: PostgreSQL as Primary Database

## Status
Accepted

## Context
Need a reliable, scalable database with good JSON support.

## Decision
Use PostgreSQL 15 with:
- UUID v7 for primary keys
- JSONB for flexible data
- Full-text search capabilities

## Consequences
- Excellent performance
- Advanced features available
- Team familiarity required

---

# ADR-005: Redis for Caching and Queues

## Status
Accepted

## Context
Need fast caching and reliable message queue.

## Decision
Use Redis for:
- Application cache
- Session storage
- Symfony Messenger transport

## Consequences
- High performance
- Single dependency for multiple uses
- Memory management required

---

# ADR-006: React with TypeScript for Frontend

## Status
Accepted

## Context
Modern, maintainable frontend with type safety.

## Decision
Use React 18 + TypeScript + Vite:
- Component-based architecture
- Type-safe development
- Fast development experience

## Consequences
- Excellent developer experience
- Type safety catches bugs early
- Learning curve for TypeScript

---

# ADR-007: JWT Authentication

## Status
Accepted

## Context
Need stateless authentication for API.

## Decision
Implement JWT with:
- Access tokens (short-lived)
- Refresh tokens (long-lived)
- Role claims for authorization

## Consequences
- Stateless and scalable
- Token management complexity
- Secure implementation required
