## 🧭 Decisions Summary

This document summarizes key technical and architectural decisions made during the design and development of the platform. For full detail and change history, see ADRs in docs/decisions/adr/.

### Approved ADRs (high level)

- ADR-001: Use Laravel 11 for Backend API — Framework choice aligned with team proficiency and ecosystem. [Read ADR](./adr/ADR-001-use-laravel-11-backend-api.md)
- ADR-002: Use Angular for Frontend — Angular CLI, RxJS services, Tailwind. [Read ADR](./adr/ADR-002-use-angular-frontend.md)
- ADR-011: Laravel Layering: Controllers→Services→Actions — Enforce thin controllers and testable units. [Read ADR](./adr/ADR-011-laravel-layering-controllers-services-actions.md)
- ADR-012: Branching Strategy: develop→staging→production — Feature branches to develop; QA on staging; releases to production. [Read ADR](./adr/ADR-012-branching-strategy-develop-staging-production.md)
- ADR-013: Estimation Feature (MVP) — Appliance- and spend-based estimation paths. [Read ADR](./adr/ADR-013-estimation-feature.md)
- ADR-014: Payment Feature — Milestone-based plans with percentage splits and progress tracking. [Read ADR](./adr/ADR-014-payment-feature.md)
- ADR-015: Quick Identity Confirmation — Email/phone verification, ID card, installer/provider credentials. [Read ADR](./adr/ADR-015-quick-identity-confirmation.md)
- ADR-016: Hardware Type Attributes — Consolidate type attributes in hardware entity. [Read ADR](./adr/ADR-016-hardware-type-attributes.md)
- ADR-017: Recommendation System Evolution — MVP rules now, AI/ML later. [Read ADR](./adr/ADR-017-full-recommendation-system.md)
- ADR-018: Organisations Model — Ownership, membership, and roles for org contexts. [Read ADR](./adr/ADR-018-organisations.md)
- ADR-019: Site Visitation & Scheduling — Availability, booking, matching, and map location. [Read ADR](./adr/ADR-019-site-visitation-scheduling.md)

### Pending/Proposed/Postponed (selected)

- ADR-003: Deployment — Postponed
- ADR-004: Escrow for Payments — Postponed
- ADR-005: Modular API Structure — Pending
- ADR-006: Caching Layer (Redis) — Proposed
- ADR-007: Terraform for IaC — Pending
- ADR-008: JWT + RBAC — Pending
- ADR-009: AWS Aurora Postgres — Pending
- ADR-010: CI/CD with GitHub Actions + CodePipeline — Pending
