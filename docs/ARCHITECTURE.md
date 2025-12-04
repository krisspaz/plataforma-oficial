# Architecture Documentation

## Overview

This document describes the architecture of the School Management Platform.

## Architecture Pattern

The system follows **Hexagonal Architecture** (Ports and Adapters) combined with **Domain-Driven Design** principles.

```
┌─────────────────────────────────────────────────────────────┐
│                      INFRASTRUCTURE                          │
│  ┌─────────────────────────────────────────────────────────┐ │
│  │                    APPLICATION                           │ │
│  │  ┌─────────────────────────────────────────────────────┐│ │
│  │  │                     DOMAIN                          ││ │
│  │  │                                                     ││ │
│  │  │  Entities  │  Value Objects  │  Domain Services    ││ │
│  │  │                                                     ││ │
│  │  └─────────────────────────────────────────────────────┘│ │
│  │                                                         │ │
│  │  Commands  │  Queries  │  Handlers  │  DTOs            │ │
│  └─────────────────────────────────────────────────────────┘ │
│                                                              │
│  Controllers  │  Repositories  │  External Services         │
└─────────────────────────────────────────────────────────────┘
```

## Bounded Contexts

### 1. Payment Context
- Payment Plans
- Installments
- Daily Closures
- Debtor Reports

### 2. Contract Context
- Contract Generation
- Digital Signatures
- PDF Generation

### 3. Coordination Context
- Teacher Assignments
- Announcements
- Calendar Events

### 4. Grades Context
- Grade Recording
- Bimester Management
- Report Cards

## CQRS Pattern

Commands and Queries are separated:

```
┌─────────────────┐       ┌─────────────────┐
│    Commands     │       │     Queries     │
│                 │       │                 │
│ CreatePayment   │       │ GetDebtors      │
│ RecordGrade     │       │ GetStudentGrades│
│ SignContract    │       │ GetAnnouncements│
└────────┬────────┘       └────────┬────────┘
         │                         │
         ▼                         ▼
┌─────────────────┐       ┌─────────────────┐
│ Command Handler │       │ Query Handler   │
└────────┬────────┘       └────────┬────────┘
         │                         │
         ▼                         ▼
┌─────────────────────────────────────────┐
│              Repository                  │
└─────────────────────────────────────────┘
```

## Data Flow

1. **Request** → Controller
2. **Controller** → Command/Query
3. **Message Bus** → Handler
4. **Handler** → Repository/Service
5. **Response** ← DTO

## Directory Structure

```
backend/
├── src/
│   ├── Domain/           # Business logic, entities
│   │   ├── Payment/
│   │   ├── Contract/
│   │   ├── Coordination/
│   │   └── Grades/
│   ├── Application/      # Use cases, CQRS
│   │   ├── Payment/
│   │   ├── Contract/
│   │   ├── Coordination/
│   │   └── Grades/
│   ├── Infrastructure/   # External adapters
│   │   ├── Payment/
│   │   ├── Contract/
│   │   ├── Coordination/
│   │   └── Grades/
│   └── Controller/       # HTTP layer
└── tests/
    ├── Unit/
    ├── Integration/
    └── Performance/
```

## Security Architecture

```
┌─────────────┐
│   Client    │
└──────┬──────┘
       │ HTTPS
       ▼
┌─────────────┐
│   Nginx     │ ◄── Rate Limiting, SSL
└──────┬──────┘
       │
       ▼
┌─────────────┐
│    JWT      │ ◄── Authentication
│   Firewall  │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│    RBAC     │ ◄── Authorization
│   Voters    │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│ Application │
└─────────────┘
```

## Roles & Permissions

| Role | Permissions |
|------|-------------|
| ADMIN | Full system access |
| SECRETARIA | Payments, Contracts, Enrollments |
| COORDINATOR | Assignments, Grades, Calendar |
| TEACHER | Grade entry, Student view |
| PARENT | Child grades, Payments |

## Technology Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 8.2, Symfony 7 |
| Frontend | React 18, TypeScript, Vite |
| Database | PostgreSQL 15 |
| Cache | Redis 7 |
| Queue | Symfony Messenger + Redis |
| Container | Docker, Docker Compose |
| CI/CD | GitHub Actions |

## Scalability Considerations

1. **Read Replicas**: PostgreSQL replicas for read-heavy operations
2. **Caching**: Redis for frequently accessed data
3. **Horizontal Scaling**: Kubernetes with HPA
4. **CDN**: Static assets via CloudFront/Cloudflare
