# Testing Guide

## Overview

This document describes how to run tests for the School Management Platform.

## Test Suites

### Backend Tests (PHPUnit)

#### Unit Tests
```bash
# Run all unit tests
docker compose exec backend vendor/bin/phpunit tests/Unit

# Run specific module
docker compose exec backend vendor/bin/phpunit tests/Unit/Domain/Payment
docker compose exec backend vendor/bin/phpunit tests/Unit/Domain/Grades
docker compose exec backend vendor/bin/phpunit tests/Unit/Domain/Contract
docker compose exec backend vendor/bin/phpunit tests/Unit/Domain/Coordination
```

#### Integration Tests
```bash
# Run all integration tests
docker compose exec backend vendor/bin/phpunit tests/Integration

# Run API tests
docker compose exec backend vendor/bin/phpunit tests/Integration/Api
```

#### Performance Tests
```bash
# Run performance benchmarks
docker compose exec backend vendor/bin/phpunit tests/Performance
```

#### Code Coverage
```bash
# Generate coverage report
docker compose exec backend vendor/bin/phpunit --coverage-html var/coverage

# View report
open var/coverage/index.html
```

### Frontend Tests (Vitest)

#### Unit/Component Tests
```bash
cd frontend

# Run all tests
npm test

# Run in watch mode
npm run test:watch

# Run with coverage
npm run test:coverage
```

#### E2E Tests
```bash
cd frontend

# Run E2E tests
npm run test:e2e

# Run specific workflow
npm run test:e2e -- --grep "Payment"
```

## Test Data

### Fixtures
```bash
# Load test fixtures
docker compose exec backend php bin/console doctrine:fixtures:load --env=test
```

### Test Users

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@test.com | test123 |
| Secretaria | secretaria@test.com | test123 |
| Coordinator | coordinador@test.com | test123 |
| Teacher | maestro@test.com | test123 |
| Parent | padre@test.com | test123 |

## CI/CD Integration

Tests run automatically on:
- Pull requests
- Pushes to main branch
- Scheduled nightly builds

### GitHub Actions
```yaml
# .github/workflows/test.yml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Run Backend Tests
        run: |
          docker compose up -d
          docker compose exec -T backend vendor/bin/phpunit
      - name: Run Frontend Tests
        run: |
          cd frontend && npm ci && npm test
```

## Best Practices

1. **Write tests first** (TDD when possible)
2. **One assertion per test** (ideally)
3. **Use descriptive test names**
4. **Mock external dependencies**
5. **Keep tests fast** (< 100ms each)
6. **Maintain test isolation**

## Coverage Goals

| Module | Current | Target |
|--------|---------|--------|
| Domain | 85% | 90% |
| Application | 75% | 85% |
| Infrastructure | 60% | 70% |
| Frontend | 50% | 70% |
