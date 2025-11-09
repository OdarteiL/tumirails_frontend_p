# Tumi Configurator Setup Guide

## Overview

The Tumi Solar Configurator is a comprehensive platform connecting customers, installers, providers, and verifiers in the solar energy ecosystem. This setup creates a minimal authentication system with Laravel backend and Angular frontend.

## Setup Options

Choose one of the following setup methods:

### Option 1: Docker Compose (Recommended)

**Prerequisites:**
- Docker 20.10+ and Docker Compose v2
- Git

**Quick Start:**
```bash
# Clone and navigate to project
git clone <repository-url>
cd tumi_configurator

# Start all services (builds images automatically)
docker compose up -d --build

# Wait for services to be healthy, then run setup
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
docker compose exec backend php artisan migrate
```

**Services:**
- Frontend: `http://localhost:4200` (Angular)
- Backend API: `http://localhost:8000` (Laravel)
- Database: PostgreSQL on `localhost:5432`
- Redis: Cache/Sessions on `localhost:6379`
- Soketi: WebSocket server on `localhost:6001`

**Features:**
- Hot reload for both frontend and backend
- Queue workers for background jobs
- Real-time notifications via WebSockets
- Redis for caching and sessions
- PostgreSQL with health checks
- CSRF-free API authentication

**Useful Commands:**
```bash
# View logs
docker compose logs -f [service_name]

# Stop services
docker compose down

# Rebuild after dependency changes
docker compose up --build

# Access backend shell
docker compose exec backend bash

# Run artisan commands
docker compose exec backend php artisan [command]

# Reset database
docker compose exec backend php artisan migrate:fresh

# Check service status
docker compose ps
```

**Troubleshooting:**
- If containers fail to start, check logs: `docker compose logs`
- For permission issues: `docker compose exec backend chown -R www-data:www-data storage bootstrap/cache`
- For database connection issues: Ensure PostgreSQL container is healthy
- For CSRF errors: API routes are configured without CSRF validation

### Option 2: Local Development

**Prerequisites:**
- PHP 8.1+ with extensions: pdo, pdo_pgsql, mbstring, xml, ctype, json, bcmath, zip
- Composer 2.0+
- Node.js 18+ with npm
- PostgreSQL 13+ or MySQL 8.0+
- Redis 6+ (optional, for caching)

**Backend Setup:**
```bash
cd backend

# Install dependencies
composer install

# Environment setup
cp .env.example .env
php artisan key:generate

# Configure database in .env:
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=tumi_configurator
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Optional Redis configuration:
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Database setup
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate

# Start development server
php artisan serve

# In separate terminal, start queue worker (optional)
php artisan queue:work
```

**Frontend Setup:**
```bash
cd frontend

# Install dependencies
npm install

# Start development server
ng serve

# Or with specific host/port
ng serve --host 0.0.0.0 --port 4200
```

**Services:**
- Frontend: `http://localhost:4200`
- Backend API: `http://localhost:8000`

**Local Development Notes:**
- Create PostgreSQL database manually: `createdb tumi_configurator`
- For MySQL, update DB_CONNECTION to `mysql` in .env
- Redis is optional but recommended for sessions and caching
- Queue worker is optional for background job processing

## Testing the Authentication Flow

1. Visit `http://localhost:4200`
2. Register a new account with:
   - First Name, Last Name
   - Email and Password
   - Role (customer/installer/provider)
3. Login with your credentials
4. Access the protected dashboard

## API Endpoints

- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - User login  
- `POST /api/auth/logout` - User logout
- `GET /api/auth/me` - Get current user

## Environment Variables

**Backend (.env):**
```env
APP_NAME="Tumi Configurator"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=tumi
DB_USERNAME=tumi
DB_PASSWORD=tumi_pwd

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis
REDIS_PORT=6379

SESSION_DOMAIN=.localhost
SESSION_SAME_SITE=lax
SESSION_SECURE_COOKIE=false
SANCTUM_STATEFUL_DOMAINS=localhost:4200,127.0.0.1:4200
```

**Frontend (environment.ts):**
```typescript
export const environment = {
  production: false,
  apiUrl: 'http://localhost:8000/api',
  wsUrl: 'ws://localhost:6001'
};
```

## Next Steps

This setup provides the foundation for:
- Site management
- Energy estimation
- Hardware recommendations
- Project management
- Payment processing

Refer to the [MVP Roadmap](docs/product/mvp/mvp-roadmap.md) for the complete development plan.

## Common Issues

**CSRF Token Mismatch:**
- API routes are configured without CSRF validation
- Ensure frontend sends proper headers: `Accept: application/json`

**Database Connection:**
- Verify database credentials in .env
- Check if database exists and is accessible
- For Docker: ensure PostgreSQL container is healthy

**Permission Errors:**
- Laravel storage and cache directories need write permissions
- Docker: `docker compose exec backend chown -R www-data:www-data storage bootstrap/cache`
- Local: `chmod -R 775 storage bootstrap/cache`

**Port Conflicts:**
- Change ports in docker-compose.yaml if needed
- Default ports: 4200 (frontend), 8000 (backend), 5432 (postgres), 6379 (redis)