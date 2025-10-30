# MVP Setup (Sprint 0 Plan)

This repository currently contains documentation only. Use this setup guide to prepare environments and scaffolding for Sprint 0.

## Prerequisites

- PHP 8.2+, Composer
- Node.js 18+, pnpm or npm
- Docker Desktop or Docker Engine
- MySQL 8+ or PostgreSQL 13+

## Backend (Laravel) – planned scaffolding

- Create `backend/` Laravel app with Sanctum, API routes, and initial migrations for MVP tables:
  - users, sites, appliances, user_appliances, categories, hardware_types, providers, hardware, installers, estimations, recommended_hardware, projects, project_hardware, payments
- Seed minimal test data for a demo flow.

## Frontend (Vue 3) – planned scaffolding

- Create `frontend/` SPA with Pinia, Vue Router, Tailwind.
- Implement flows: Auth → Sites → Estimation → Recommendation → Project → Payment.

## Developer experience

- Add `docker-compose.yml` for local MySQL/Postgres, Redis, Laravel API, and Vue dev server.
- Setup GitHub Actions for lint/test (PHPUnit, ESLint) and basic build checks.

## Data model alignment

- Import and review `docs/architecture/data-models/tumi.dbml`.
- Keep OpenAPI and migrations aligned; generate API clients if helpful.

## Milestone demo

- By end of Sprint 0, aim for a local demo where a customer logs in, creates a site, adds appliances, generates an estimation, sees recommendations, creates a project, and makes a mock payment.
