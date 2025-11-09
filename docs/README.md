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
  - Vision: [overview/vision.md](overview/vision.md)
  - Stakeholders: [overview/stakeholders.md](overview/stakeholders.md)
  - Glossary: [overview/glossary.md](overview/glossary.md)
  - Non-Functional Requirements: [overview/non-functional-requirements.md](overview/non-functional-requirements.md)
- Product
  - MVP Roadmap: [product/mvp/mvp-roadmap.md](product/mvp/mvp-roadmap.md)
  - MVP Setup (Sprint 0): [product/mvp/setup.md](product/mvp/setup.md)
  - User Stories (full): [product/user-stories.md](product/user-stories.md)
  - MVP User Stories (subset): [product/mvp/mvp-user-stories.md](product/mvp/mvp-user-stories.md)
  - Sprint Plan (2 x 1-week sprints): [product/mvp/sprint-plan.md](product/mvp/sprint-plan.md)
  - Jira Backlog (CSV): [product/mvp/jira-backlog.csv](product/mvp/jira-backlog.csv)
- Architecture
  - Technical Architecture: [architecture/technical-architecture.md](architecture/technical-architecture.md)
  - System Architecture (Mermaid): [architecture/system-architecture.mmd](architecture/system-architecture.mmd)
  - Deployment View: [architecture/deployment-view.md](architecture/deployment-view.md)
  - Data Models:
  - Mermaid ER: [architecture/data-models/data model.mmd](architecture/data-models/data%20model.mmd)
  - DBML source: [architecture/data-models/tumi.dbml](architecture/data-models/tumi.dbml)
  - Database Schema summary: [architecture/database-schema.md](architecture/database-schema.md)
-- API
  - OpenAPI spec: [api/openapi.yaml](api/openapi.yaml)
  - API Endpoints Guide: [api/api-endpoints.md](api/api-endpoints.md)
-- Decisions & Governance
  - Decision Log: [decisions/decisions-summary.md](decisions/decisions-summary.md)
  - ADRs: [decisions/adr/adr-set.md](decisions/adr/adr-set.md) and template [decisions/adr/adr-template.md](decisions/adr/adr-template.md)
  - Technical Review Report (TRR):
    - Template: [governance/trr/technical-review-report-template.md](governance/trr/technical-review-report-template.md)
    - Example: [governance/trr/technical-review-report.md](governance/trr/technical-review-report.md)
  - Contribution Guide: [governance/contribution-guide.md](governance/contribution-guide.md)
  - Coding Standards: [governance/coding-standards.md](governance/coding-standards.md)
  - AI Assistance Guide: [governance/ai-assistance.md](governance/ai-assistance.md)
  - Branching Strategy: [governance/branching-strategy.md](governance/branching-strategy.md)

## Viewing the diagrams

- Mermaid `.mmd` files can be previewed in VS Code with a Mermaid extension or rendered in Markdown-compatible viewers.
- DBML (`tumi.dbml`) can be imported at https://dbdiagram.io.

## Notes

- This repository is documentation-first. The `backend/` and `frontend/` folders in the root README are part of the target structure for Sprint 0 scaffolding.
- A `docs/trash/` folder holds uncurated or legacy assets to keep the main narrative clean.

## Using AI assistance

- When asking AI for help, always attach the relevant context:
  - Backend: [backend/AI_CONTEXT.md](../backend/AI_CONTEXT.md)
  - Frontend: [frontend/AI_CONTEXT.md](../frontend/AI_CONTEXT.md)
- Include file paths, constraints, acceptance criteria, and ask for tests.

## Next steps for Sprint 0

- Finalize ERD from `tumi.dbml` and align OpenAPI payloads.
- Scaffold backend (Laravel) and frontend (Angular) repos or subfolders.
- Set up CI, environments, and seed data for a demo flow.

