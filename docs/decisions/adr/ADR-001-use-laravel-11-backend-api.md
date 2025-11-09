# ADR-001: Use Laravel 11 for Backend API

## Status
Approved

## Context
We need a backend framework that accelerates development of an API-centric application with rich data modeling (ORM), authentication support, queues, caching, and testing utilities. The team has prior experience with Laravel, and the ecosystem provides first-class tooling for Sanctum (API tokens), Horizon (queues), and Eloquent ORM.

## Decision
Adopt Laravel 11 as the backend framework for the API layer.

## Rationale
- Mature ecosystem and documentation
- Eloquent simplifies relational modeling
- First-party packages (Sanctum, Horizon, Scout, etc.) reduce integration overhead
- Built-in testing framework speeds up automated test adoption
- Team proficiency reduces onboarding cost

## Considered Alternatives
| Alternative | Reason Rejected |
|-------------|-----------------|
| NestJS (TypeScript) | Less team experience; migration of PHP skillset would slow MVP delivery |
| Django (Python) | Strong, but lower team proficiency; alignment with existing PHP tooling preferred |
| Spring Boot (Java) | Heavier stack than required for early iteration |

## Consequences
- PHP skillset required for backend contributors
- Must enforce architectural layering (Controllers → Services → Actions) to avoid monolithic controllers
- Need containerized dev environment with PHP extensions (pdo_pgsql, etc.)

## Follow-up Actions
- Scaffold Laravel with Sanctum authentication (completed)
- Document layering (ADR-011) and branching (ADR-012)
- Add automated tests for core domains in early sprints
