# MVP Sprint Plan — Two 1-week Sprints

Goal: Deliver an end-to-end MVP flow from estimation to upfront payment in two consecutive one-week sprints.

## Sprint 1 (Week 1): Foundation & Estimation
Outcome: Customer can sign up, create a site, add appliances, get estimation and recommendations.

Epics
- E1: Auth & Foundation
- E2: Sites & Appliances
- E3: Estimation & Recommendation
- E4: DevEx & CI (lightweight)

Stories (Jira-like summary)
- [E1] Auth: Registration/Login (API + UI); DoD: token-based auth via Sanctum, basic UI
- [E2] Create Site (API + UI); DoD: store name/address/coords; single site
- [E2] Appliance Catalog (seed + list API); DoD: admin-managed default set
- [E2] Add User Appliances (API + UI); DoD: link appliances to site
- [E3] Estimation Engine (service + API); DoD: total kW, daily kWh, monthly cost
- [E3] Recommendations (service + API); DoD: panel/inverter/battery suggestion from catalog
- [E4] Seed data & local demo script; DoD: demo account, sample hardware

Risks/Dependencies
- Estimation formulas and assumptions must be agreed
- Hardware catalog completeness for realistic recs

## Sprint 2 (Week 2): Project & Payment
Outcome: Customer can create a project from estimation, admin can assign installer, customer can pay upfront.

Epics
- E5: Project Creation & Assignment
- E6: Payments (Upfront)
- E7: Admin Basics

Stories
- [E5] Create Project from Estimation (API + UI); DoD: link to site/customer
- [E5] Installer Assignment (Admin) (API + UI); DoD: manual assignment flow
- [E6] Upfront Payment (API + UI); DoD: call gateway mock/live, record payment
- [E7] Admin: Manage Hardware Types/Categories (API + UI); DoD: basic CRUD
- [E7] Admin: User Management (activate/suspend) minimal
- [E7] E2E Demo Path (script + data); DoD: demo scenario runnable

Risks/Dependencies
- Gateway availability and credentials
- Clarify enums and statuses for projects and payments

## Capacity and Timeline
- Assumption: 3–5 engineers; story points in `jira-backlog.csv` sized for two 1-week sprints.
- Definition of Done: documented in Meeting Moderation Guide.

## Deliverables Checklist
- End-to-end demo video or script
- OpenAPI updated and validated
- Seed data and instructions
- TRR updated; ADRs captured for decisions made
