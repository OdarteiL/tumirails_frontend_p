# Tumi Configurator - Backend API

[![CI Pipeline](https://github.com/tumirailsdotcom/tumi_configurator/workflows/CI%20Pipeline/badge.svg)](https://github.com/tumirailsdotcom/tumi_configurator/actions)

Laravel 12 backend API for the Tumi Solar Configurator platform.

## Features

- **RESTful API** - Clean API endpoints following REST conventions
- **Authentication** - Laravel Sanctum for API token authentication
- **Service Layer Architecture** - Controllers → Services → Actions pattern
- **Comprehensive Testing** - 96%+ code coverage with PHPUnit
- **Code Quality** - Laravel Pint for consistent code style
- **Database** - PostgreSQL with migrations and seeders
- **Caching** - Redis for sessions and cache
- **Broadcasting** - Real-time events with Soketi

## Tech Stack

- **PHP** 8.3
- **Laravel** 12.x
- **PostgreSQL** 16
- **Redis** 7
- **Sanctum** - API authentication
- **PHPUnit** - Testing framework
- **Laravel Pint** - Code style fixer

## Getting Started

### Prerequisites

- Docker & Docker Compose
- Git

### Installation

1. Clone the repository:
```bash
git clone https://github.com/tumirailsdotcom/tumi_configurator.git
cd tumi_configurator
```

2. Start the development environment:
```bash
docker compose up -d
```

3. The backend API will be available at `http://localhost:8000`

Database migrations run automatically on container startup.

### Environment Variables

Key environment variables (see `.env.example`):

```env
APP_URL=http://localhost:8000
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_DATABASE=tumi
REDIS_HOST=redis
SESSION_DOMAIN=.localhost
SANCTUM_STATEFUL_DOMAINS=localhost:4200,127.0.0.1:4200
```

## Development

### Running Tests

```bash
# Run all tests
docker compose exec backend php artisan test

# Run tests with coverage
docker compose exec backend php artisan test --coverage --min=80

# Run specific test file
docker compose exec backend php artisan test tests/Feature/Http/Controllers/Api/AuthControllerTest.php
```

### Code Style

This project uses Laravel Pint for code style consistency:

```bash
# Check code style
docker compose exec backend ./vendor/bin/pint --test

# Fix code style issues
docker compose exec backend ./vendor/bin/pint
```

### Database Migrations

```bash
# Run migrations
docker compose exec backend php artisan migrate

# Rollback migrations
docker compose exec backend php artisan migrate:rollback

# Fresh migration with seeders
docker compose exec backend php artisan migrate:fresh --seed
```

## API Documentation

API endpoints are documented using OpenAPI 3.1 specification.

See [`/docs/api/openapi.yaml`](../docs/api/openapi.yaml) for complete API documentation.

### Authentication Endpoints

- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout (authenticated)
- `GET /api/auth/me` - Get authenticated user (authenticated)

## Architecture

This project follows a clean architecture pattern:

```
Controllers → Services → Actions
```

- **Controllers** - Handle HTTP requests/responses
- **Services** - Orchestrate business logic
- **Actions** - Single-responsibility units of work

### Project Structure

```
app/
├── Actions/         # Single-purpose action classes
├── Http/
│   ├── Controllers/ # HTTP request handlers
│   ├── Requests/    # Form request validation
│   └── Resources/   # API resource transformers
├── Models/          # Eloquent models
└── Services/        # Business logic orchestrators
```

## CI/CD Pipeline

This project uses GitHub Actions for continuous integration.

### Automated Checks

On every push and pull request:

1. **Code Style** - Laravel Pint verification
2. **Unit Tests** - PHPUnit with 80% coverage requirement
3. **Build Validation** - Production Docker image builds
4. **Migration Check** - Database migrations tested against PostgreSQL

### Running CI Checks Locally

```bash
# Code style check
./vendor/bin/pint --test

# Run tests with coverage
php artisan test --coverage --min=80

# Build production image
docker build -f Dockerfile.prod -t backend:test .
```

See [CI/CD Documentation](../docs/CI-CD.md) for detailed pipeline information.

## Production Deployment

### Building Production Image

```bash
docker build -f Dockerfile.prod -t tumi-backend:latest .
```

The production Dockerfile:
- Installs production dependencies only
- Optimizes autoloader
- Caches configuration, routes, and views
- Runs as non-root user

### Environment Configuration

Set `RUN_MIGRATIONS=true` to run migrations on container startup.

## Contributing

1. Create a feature branch from `develop`
2. Make your changes following the architecture patterns
3. Ensure tests pass: `php artisan test`
4. Ensure code style is correct: `./vendor/bin/pint`
5. Submit a pull request to `develop`

### Code Standards

- Follow PSR-12 coding standards (enforced by Pint)
- Write tests for new features (minimum 80% coverage)
- Use type hints and return types
- Follow the Controllers → Services → Actions pattern

## License

This project is proprietary software. All rights reserved.
