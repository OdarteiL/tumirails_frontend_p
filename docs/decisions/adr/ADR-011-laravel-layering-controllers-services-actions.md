# ADR-011: Laravel Layering — Controllers → Services → Actions

Date: 2025-10-30
Status: Approved
Owner: Engineering Lead

## 1. Context
We need a consistent backend structure that keeps controllers thin, isolates orchestration from units of work, supports testability, and scales as features grow. Previous patterns risked fat controllers or monolithic services, hurting clarity and tests.

## 2. Decision
Adopt a layering pattern in Laravel:
- Controllers: HTTP concerns only; delegate to services; validate via Form Requests; return API Resources
- Services: Orchestrate a single use-case; compose actions; handle transactions and cross-entity concerns
- Actions: Small, single-purpose units (e.g., CreateEstimationAction) with at most two parameters (use DTOs for more data)

Cross-cutting rules:
- Functions are small and single-purpose; ≤2 parameters per function (use DTOs/value objects otherwise)
- Explicit error handling; domain exceptions mapped to HTTP responses
- Use typed properties/returns; prefer DTOs over arrays between layers

## 3. Alternatives Considered
| Option | Pros | Cons |
|-------|------|------|
| Fat Controllers | Simple to start | Hard to test, poor separation of concerns |
| Services-only | Centralized logic | Services become monoliths; unclear unit boundaries |
| DDD with Repositories everywhere | Rich domain isolation | Heavy for MVP timeline; more boilerplate |

## 4. Consequences
- + Improved testability (Actions and Services are easily unit-testable)
- + Clear responsibilities and faster onboarding
- − Slightly more files/boilerplate; requires discipline

## 5. Related Decisions
- ADR-005 Modular API Structure
- ADR-006 Implement Caching Layer (Redis)
