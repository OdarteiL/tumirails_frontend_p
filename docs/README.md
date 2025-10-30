# Documentation Guide

Use this page as the single entry point to understand the product vision, MVP scope, architecture, API, and governance. It’s structured for quick onboarding and team alignment.

## How to use this documentation

- Start with Overview to understand the why and who.
- Review Product to see MVP scope, roadmap, and user stories.
- Skim Architecture for how the system fits together.
- Use API and Data Model when defining contracts and persistence.
- Check Decisions and Governance for the why and how we change things.

## Table of contents

- Overview
  - Vision: `docs/overview/vision.md`
  - Stakeholders: `docs/overview/stakeholders.md`
  - Glossary: `docs/overview/glossary.md`
  - Non-Functional Requirements: `docs/overview/non-functional-requirements.md`
- Product
  - MVP Roadmap: `docs/product/mvp/mvp-roadmap.md`
  - MVP Setup (Sprint 0): `docs/product/mvp/setup.md`
  - User Stories (full): `docs/product/user-stories.md`
  - MVP User Stories (subset): `docs/product/mvp/mvp-user-stories.md`
  - Sprint Plan (2 x 1-week sprints): `docs/product/mvp/sprint-plan.md`
  - Jira Backlog (CSV): `docs/product/mvp/jira-backlog.csv`
- Architecture
  - Technical Architecture: `docs/architecture/technical-architecture.md`
  - System Architecture (Mermaid): `docs/architecture/system-architecture.mmd`
  - Deployment View: `docs/architecture/deployment-view.md`
  - Data Models:
    - Mermaid ER: `docs/architecture/data-models/data model.mmd`
    - DBML source: `docs/architecture/data-models/tumi.dbml`
    - Database Schema summary: `docs/architecture/database-schema.md`
- API
  - OpenAPI spec: `docs/api/openapi.yaml`
  - API Endpoints Guide: `docs/api/api-endpoints.md`
- Decisions & Governance
  - Decision Log: `docs/decisions/decisions-summary.md`
  - ADRs: `docs/decisions/adr/adr-set.md` and template `docs/decisions/adr/adr-template.md`
  - Technical Review Report (TRR):
    - Template: `docs/governance/trr/technical-review-report-template.md`
    - Example: `docs/governance/trr/technical-review-report.md`
  - Contribution Guide: `docs/governance/contribution-guide.md`
  - Coding Standards: `docs/governance/coding-standards.md`
  - AI Assistance Guide: `docs/governance/ai-assistance.md`
  - Branching Strategy: `docs/governance/branching-strategy.md`

## Viewing the diagrams

- Mermaid `.mmd` files can be previewed in VS Code with a Mermaid extension or rendered in Markdown-compatible viewers.
- DBML (`tumi.dbml`) can be imported at https://dbdiagram.io.

## Notes

- This repository is documentation-first. The `backend/` and `frontend/` folders in the root README are part of the target structure for Sprint 0 scaffolding.
- A `docs/trash/` folder holds uncurated or legacy assets to keep the main narrative clean.

## Using AI assistance

- When asking AI for help, always attach the relevant context:
  - Backend: `backend/AI_CONTEXT.md`
  - Frontend: `frontend/AI_CONTEXT.md`
- Include file paths, constraints, acceptance criteria, and ask for tests.

## Next steps for Sprint 0

- Finalize ERD from `tumi.dbml` and align OpenAPI payloads.
- Scaffold backend (Laravel) and frontend (Vue) repos or subfolders.
- Set up CI, environments, and seed data for a demo flow.

