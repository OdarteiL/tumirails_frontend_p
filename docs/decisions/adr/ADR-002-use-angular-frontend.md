# ADR-002: Use Angular for Frontend

## Status
Approved (supersedes earlier Vue 3 consideration)

## Context
Initial discussions evaluated multiple SPA frameworks (Vue, React, Angular). While Vue was first trialed for rapid prototyping, long‑term maintainability, opinionated structure, and enterprise scaling needs favor Angular. The team has sufficient TypeScript experience and prefers stronger conventions as feature set grows (estimation flows, project management, scheduling, payments, dashboards).

## Decision
Adopt Angular (version 17+) with:
- Angular Router for module lazy loading
- TailwindCSS for styling
- RxJS for reactive data streams
- Optional NgRx (post-MVP) if shared state complexity increases
- Interceptors for auth token and error handling

## Rationale
- Opinionated structure reduces architectural drift
- Type safety and DI simplify scaling features
- Built-in testing (Jasmine/Karma) and tooling (CLI) reduce setup time
- Mature ecosystem for forms, routing, i18n, accessibility

## Considered Alternatives
| Alternative | Reason Rejected |
|------------|-----------------|
| Vue 3 | Lightweight but less opinionated; risk of divergent patterns over time |
| React | Requires assembling more tooling; less out-of-box structure |

## Consequences
- Team members new to Angular need ramp-up time (provide curated learning path)
- Slightly higher initial boilerplate versus lighter frameworks
- Must enforce module boundaries to prevent a monolith module

## Follow-up Actions
- Scaffold Angular app in `frontend/`
- Update documentation (tech stack, ADR references)
- Align frontend build & Dockerfile with Angular CLI
- Define coding standards extension for Angular style (component naming, selector prefixes)
