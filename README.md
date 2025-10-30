# Tumi Solar Configurator

A comprehensive solar energy system configurator and project management platform built with Laravel (backend) and Vue.js (frontend).

## Overview

The Tumi Solar Configurator is a multi-stakeholder platform that connects customers, installers, providers, and verifiers in the solar energy ecosystem. It provides energy estimation, hardware recommendation, project management, and payment processing capabilities.

## Quick Start

- **New to the project?** Start with [Documentation Guide](docs/README.md)
- **Ready to develop?** Check [MVP Roadmap](docs/product/mvp/mvp-roadmap.md)
- **Need API reference?** See [API Documentation](docs/api/api-endpoints.md)
- **MVP User Stories (subset)**: [MVP Stories](docs/product/mvp/mvp-user-stories.md)
- **Two 1-week sprints plan**: [Sprint Plan](docs/product/mvp/sprint-plan.md)
- **Need API reference?** See [API Endpoints Guide](docs/api/api-endpoints.md) and [OpenAPI](docs/api/openapi.yaml)
- **Branching strategy**: [develop → staging → production](docs/governance/branching-strategy.md)
- **Using AI help?** Read [AI Assistance Guide](docs/governance/ai-assistance.md)

## Key Features

### Core Platform (MVP)
- **Energy Estimation**: Calculate power requirements based on appliances and usage patterns
- **Hardware Recommendation**: System-generated suggestions for solar panels, inverters, and batteries
- **Project Management**: End-to-end project tracking from initiation to completion
- **Multi-stakeholder Workflow**: Coordinated processes for customers, installers, and providers
- **Payment Processing**: Secure payment processing with gateway integration

### Advanced Features (Post-MVP)
- **Verification System**: Quality assurance through certified verifiers
- **Advanced Payment Plans**: Milestone-based payments with automated splitting
- **AI-Powered Recommendations**: Machine learning for optimal hardware selection
- **Mobile Applications**: Native iOS and Android apps
- **Analytics Dashboard**: Business intelligence and reporting

## Stakeholders

### 1. Customer
Property owners seeking solar energy solutions who can create sites, estimate energy needs, and initiate projects.

### 2. Provider
Solar hardware suppliers and distributors who manage inventory, pricing, and hardware specifications.

### 3. Installer
Certified solar installation professionals who execute installation projects and manage timelines.

### 4. Verifier *(Post-MVP)*
Quality assurance professionals who verify installations meet standards and specifications.

### 5. Admin
Platform administrators who manage users, categories, and system configurations.

## Technology Stack

- **Backend**: Laravel 10+ (PHP 8.1+)
- **Frontend**: Vue.js 3 with Composition API
- **Database**: MySQL 8.0+ / PostgreSQL 13+
- **Payment**: Paystack (primary), Flutterwave, Stripe
- **File Storage**: AWS S3 (production) / Local (development)
- **Authentication**: Laravel Sanctum
- **Caching**: Redis
- **Queue**: Redis/Database

## Project Structure

This repository is documentation-first. Backend/Frontend scaffolding will be created in Sprint 0. Current layout:

```
tumi_configurator/
├── backend/
│   └── AI_CONTEXT.md            # Backend AI assistance context
├── frontend/
│   └── AI_CONTEXT.md            # Frontend AI assistance context
├── docs/
│   ├── README.md                # Docs index (start here)
│   ├── api/
│   │   └── openapi.yaml         # API contract (OpenAPI 3.1)
│   ├── architecture/
│   │   ├── technical-architecture.md
│   │   ├── system-architecture.mmd
│   │   ├── deployment-view.md
│   │   └── data-models/
│   │       └── tumi.dbml        # ERD source (DBML)
│   ├── decisions/
│   │   └── adr/                 # ADRs including ADR-011, ADR-012
│   ├── governance/
│   │   ├── ai-assistance.md
│   │   ├── branching-strategy.md
│   │   ├── contribution-guide.md
│   │   └── trr/
│   │       └── technical-review-report.md
│   └── product/
│       └── mvp/
│           ├── mvp-roadmap.md
│           ├── mvp-user-stories.md
│           ├── sprint-plan.md
│           └── jira-backlog.csv
└── docker-compose.yaml          # Local dev (to be used post-scaffold)
```

## Development Phases

### Phase 1: MVP (Weeks 1-12)
Core functionality for end-to-end solar project workflow

### Phase 2: Enhanced UX (Weeks 13-18)
Image management, installer marketplace, advanced estimation

### Phase 3: Quality Assurance (Weeks 19-24)
Verification system, advanced project management

### Phase 4: Financial Features (Weeks 25-30)
Advanced payments, wallet system, automated payouts

### Phase 5+: Advanced Features
AI/ML integration, mobile apps, business intelligence

## Getting Started

1. **Clone the repository**
2. **Read the [Documentation Guide](docs/README.md)**
3. **Follow the [MVP Setup Guide](docs/product/mvp/setup.md)**
4. **Review the [Database Schema](docs/architecture/database-schema.md)**
5. **Start with [MVP Development](docs/product/mvp/mvp-roadmap.md)**

## Architecture & Standards

- Backend layering: Controllers → Services → Actions (see ADR-011)
	- Keep controllers thin; orchestration in services; small single-purpose actions
	- Functions should do one thing; aim for ≤ 2 parameters (use DTOs when needed)
- Branching strategy: develop → staging → production (see ADR-012 and docs/governance/branching-strategy.md)
- See coding standards and contribution guide under `docs/governance/`

## Data Model & API

- ERD source (DBML): `docs/architecture/data-models/tumi.dbml` (import on dbdiagram.io)
- Database schema summary: `docs/database-schema.md`
- API contract: `docs/api/openapi.yaml` and `docs/api/api-endpoints.md`

## AI Assistance

- When asking AI for help, attach the relevant context:
	- Backend: `backend/AI_CONTEXT.md`
	- Frontend: `frontend/AI_CONTEXT.md`
- See `docs/governance/ai-assistance.md` for prompt checklist

## License

This project is proprietary software. All rights reserved.
