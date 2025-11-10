# Product Requirements Document (PRD) — Tumi Solar Configurator

Version: 0.1
Date: 2025-11-10
Author: Product Team

## 1. Overview
A documentation-first configurator platform to help customers estimate energy needs, receive hardware recommendations, and manage installation projects end-to-end. The MVP focuses on the core flow: estimation → recommendation → project → upfront payment.

## 2. Purpose & Scope
Purpose: Deliver an MVP that proves the core business flow for customers, providers, and installers. The scope includes authentication, site and appliance management, estimation engine, hardware recommendation, project creation, and upfront payment.

Out of scope for MVP: milestone payments, wallets, advanced verification, image/document management, full installer marketplace.

## 3. Goals & Success Metrics
- Customers can complete a full estimation and receive hardware recommendations (Primary KPI).
- Customers can create a project and make an upfront payment.
- End-to-end demo runs with seed data.
- System supports 100+ concurrent users during testing window.
- Demo playback and E2E tests available.

## 4. Stakeholders
- Product Manager
- Backend Engineers (Laravel)
- Frontend Engineers (Angular)
- QA / Test Engineers
- DevOps / Infra
- Designers
- Business / Operations (payments, providers)

## 5. User Personas
- Customer: Homeowner seeking solar configuration.
- Provider: Hardware supplier who lists equipment and pricing.
- Installer: Certified installer assigned to projects.
- Admin: Platform operator who manages catalogs and users.

## 6. Key Features (MVP)
- User registration, login (Sanctum token-based)
- Site creation (name, address, coordinates)
- Appliance catalog and user appliance selection
- Estimation calculations (kW, daily kWh, monthly cost)
- Hardware recommendation (panel, inverter, battery)
- Create project from estimation
- Admin manual installer assignment
- Upfront payment flow (gateway integration)
- Seed data and demo path

## 7. Post‑MVP Highlights (planned)
- Verification System & Identification (third-party KYC)
- Advanced payment plans with milestones & splits
- Installer marketplace & bidding
- AI-powered recommendations (ML)
- Mobile apps and analytics dashboard

## 8. Functional Requirements
- FR-001: Registration API and UI; returns token (Sanctum).
- FR-002: Create Site API; validate address/coords.
- FR-003: Appliance catalog endpoints (seed + CRUD for admin).
- FR-004: Estimation service API; persist estimations.
- FR-005: Recommendation API; tie recommendations to provider hardware.
- FR-006: Project creation API; link to estimation and site.
- FR-007: Payment API; mock gateway for MVP and record transactions.
- FR-008: Admin endpoints for hardware types and user management.

## 9. Non‑Functional Requirements
- NFR-001: Availability — target 99% during demo windows (SLOs documented in `docs/overview/non-functional-requirements.md`).
- NFR-002: Performance — handle 100+ concurrent test users.
- NFR-003: Security — protect PII, secure file uploads, tokens via HTTPS.
- NFR-004: Observability — logs and basic metrics for demo.

## 10. Data Model & API
- Source of truth: `docs/architecture/data-models/tumi.dbml` and `docs/api/openapi.yaml`.
- Key entities: users, sites, appliances, estimations, recommended_hardware, projects, payments.

## 11. Acceptance Criteria (per epic)
- Auth & Foundation: registration/login working, tokens issued, UI forms validate.
- Sites & Appliances: site creation and appliance selection persist and appear in UI.
- Estimation & Recommendation: estimation returns numerical outputs and recommendations from catalog.
- Project & Payment: project created from estimation, payment accepted (mock gateway) and status updated.

## 12. Roadmap & Sprint Plan
Two 1‑week sprints: Sprint 1 focuses on Auth, Sites & Appliances, Estimation + Recommendation; Sprint 2 focuses on Project creation, Admin assignment, and Upfront Payment. See `docs/product/mvp/sprint-plan.md` and `docs/product/mvp/jira-backlog.csv` for story-level breakdown.

## 13. Risks & Mitigations
- Risk: Payment gateway issues — Mitigation: build with gateway mock and feature flag.
- Risk: Incomplete hardware catalog — Mitigation: seed reasonable defaults and make catalog editable by admin.
- Risk: Data privacy for identity verification — Mitigation: defer third‑party KYC to post‑MVP or use secure providers.

## 14. Rollout & Launch
- Local demo via docker compose for internal demo.
- Prepare demo script and record short walkthrough video.
- After MVP validation, create staging environment and deploy via CI/CD.

## 15. Appendices
- Links:
  - Docs index: `docs/README.md`
  - MVP roadmap: `docs/product/mvp/mvp-roadmap.md`
  - Sprint plan: `docs/product/mvp/sprint-plan.md`
  - Jira backlog CSV: `docs/product/mvp/jira-backlog.csv`
  - OpenAPI: `docs/api/openapi.yaml`
  - ERD (DBML): `docs/architecture/data-models/tumi.dbml`

*** End of PRD ***
