# CI/CD Pipeline Documentation

## Overview

This project uses GitHub Actions for continuous integration and deployment. The CI pipeline automatically runs on every push and pull request to the `develop` and `main` branches.

## Pipeline Jobs

### 1. Backend Linting (`backend-lint`)
- **Purpose**: Ensures code style consistency using Laravel Pint
- **Runs**: Laravel Pint with `--test` flag
- **Failure Criteria**: Any code style violations
- **Fix**: Run `./vendor/bin/pint` locally to auto-fix issues

### 2. Backend Tests (`backend-test`)
- **Purpose**: Runs PHPUnit test suite
- **Services**: PostgreSQL 16, Redis 7
- **Coverage**: Minimum 80% required
- **Output**: Coverage reports uploaded as artifacts
- **Failure Criteria**: 
  - Any test failures
  - Coverage below 80%

### 3. Backend Build (`backend-build`)
- **Purpose**: Validates production Docker build and migrations
- **Tests**:
  - Production Docker image builds successfully
  - Database migrations run without errors
  - Application connects to PostgreSQL database
- **Failure Criteria**: Build or migration failures

### 4. Frontend Linting (`frontend-lint`)
- **Purpose**: Ensures code quality using ESLint
- **Runs**: Angular ESLint with accessibility checks
- **Failure Criteria**: Any linting errors
- **Fix**: Run `npm run lint -- --fix` locally

### 5. Frontend Build (`frontend-build`)
- **Purpose**: Validates production build
- **Tests**:
  - Production bundle builds successfully
  - Production Docker image builds successfully
- **Failure Criteria**: Build failures

### 6. Integration Check (`integration-check`)
- **Purpose**: Final verification that all checks passed
- **Depends On**: All previous jobs
- **Failure Criteria**: Any dependent job failure

## Running Tests Locally

### Backend

```bash
# Run tests
docker compose exec backend php artisan test

# Run tests with coverage
docker compose exec backend php artisan test --coverage --min=80

# Run Pint linter
docker compose exec backend ./vendor/bin/pint --test

# Fix code style
docker compose exec backend ./vendor/bin/pint
```

### Frontend

```bash
# Install dependencies
cd frontend && npm ci

# Run linter
npm run lint

# Fix linting issues
npm run lint -- --fix

# Build production
npm run build
```

## Production Docker Builds

### Backend

```bash
docker build -f backend/Dockerfile.prod -t tumi-backend:prod backend/
```

### Frontend

```bash
docker build -f frontend/Dockerfile.prod -t tumi-frontend:prod frontend/
```

## Environment Variables for CI

The following secrets should be configured in GitHub repository settings:

- (Currently none required - all tests use test databases)

## Troubleshooting

### Pint Failures
If Pint fails in CI, run locally:
```bash
docker compose exec backend ./vendor/bin/pint
git add .
git commit -m "fix: code style"
```

### Test Failures
Check test output in GitHub Actions logs. Run locally:
```bash
docker compose exec backend php artisan test --filter TestName
```

### Build Failures
Check Docker build logs. Test locally:
```bash
docker build -f backend/Dockerfile.prod backend/
docker build -f frontend/Dockerfile.prod frontend/
```

## CI Status Badge

Add to README.md:
```markdown
![CI Pipeline](https://github.com/tumirailsdotcom/tumi_configurator/workflows/CI%20Pipeline/badge.svg)
```
