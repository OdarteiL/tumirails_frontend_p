# Meeting Moderation Guide — Alignment, Decisions, and Sprint Planning

Use this guide to run a 90-minute alignment session to finalize MVP scope, close open decisions, and plan two one-week sprints.

## Roles
- Moderator (keeps time and objectives on track)
- Decider (PM/EM; breaks ties and confirms decisions)
- Scribe (captures outcomes in TRR/ADRs and backlog)
- SMEs (Backend, Frontend, Data, DevOps, Security)

## Pre-reads (sent 24h before)
- docs/README.md (docs index)
- Product: MVP Roadmap, MVP User Stories (subset)
- Architecture: Technical Architecture, ERD (DBML), OpenAPI
- Decisions: ADR set, Decisions Summary; TRR (example)

## Agenda (90 minutes)
1. Opening and goals (5m)
   - Goal: confirm MVP scope, close critical decisions, commit to two 1-week sprints.
2. Product overview and MVP scope (10m)
   - Review MVP user stories subset; confirm scope/out-of-scope.
3. Architecture & data model checkpoints (15m)
   - Validate ERD alignment for MVP tables; confirm any link choices (e.g., PaymentPlan direction, Estimation→Project link).
4. API surface for MVP (10m)
   - Confirm endpoints covered in openapi.yaml; list gaps.
5. Decision log (20m)
   - Walk through unresolved decisions; use DACI/RACI to assign Owner and Decider.
   - For each: capture Decision, Rationale, Consequences, Next Steps in ADR/TRR.
6. Sprint plan (25m)
   - Capacity check; Definition of Done.
   - Order backlog; assign stories to Sprint 1 and 2.
   - Identify blockers, dependencies, and risks.
7. Close (5m)
   - Recap decisions and owners; confirm timelines; share next steps.

## Timeboxing & facilitation tips
- Parking lot: capture tangents for follow-up.
- Decision rule: If not decided in 10 minutes, assign Owner + due date and move on.
- Document in real-time: Scribe updates ADR/TRR and backlog during meeting.

## Artifacts to update during/after meeting
- TRR: `docs/governance/trr/technical-review-report.md` (or copy the template)
- ADRs: `docs/decisions/adr/` (one per decision; set status)
- Backlog: `docs/product/mvp/jira-backlog.csv`
- Sprint Plan: `docs/product/mvp/sprint-plan.md`

## Decision checklist (MVP-critical)
- PaymentPlan ↔ Project linkage direction (choose one; recommend payment_plans.project_id UNIQUE)
- Estimation ↔ Project linkage (add projects.estimation_id or keep implicit? choose and document)
- Enum sets (roles, statuses, payment_method) aligned across DB and OpenAPI
- Image management deferred (confirm MVP deferral)

## Two one-week sprints — planning guidance
- Sprint 1 goal: Foundation + Estimation path to recommendations (happy path)
- Sprint 2 goal: Project creation + upfront payment + basic admin flows

Definition of Done (DoD) for MVP stories
- API endpoints implemented and documented in OpenAPI
- Minimal UI flow implemented and demoable
- Unit/feature tests for core logic
- Seed data available
- Happy path E2E tested locally

## RACI/DACI quick reference
- RACI: Responsible (implements), Accountable (approves), Consulted (SME), Informed (stakeholders)
- DACI: Driver (pushes decision), Approver (decider), Contributors (SMEs), Informed (stakeholders)
