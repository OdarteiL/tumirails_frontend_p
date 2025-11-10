# Database Schema Summary

This document summarizes the core entities and relationships for the Tumi Configurator and links to the source DBML and ERD.

- Source DBML: `docs/architecture/data-models/tumi.dbml` (importable at dbdiagram.io)
- Mermaid ER: `docs/architecture/data-models/data model.mmd`
- External ERD link: https://dbdiagram.io/d/Tumi-ERD-6900316d357668b732e68af5

## Core domains

- Identity & Profiles: users, installers, providers, verifiers
- Sites & Appliances: sites, appliances, user_appliances, categories
- Estimation & Recommendations: estimations, recommended_hardware
- Projects & Execution: projects, project_hardware
- Payments (Post-MVP advanced): payment_plans, payment_milestones, payments, payment_splits, wallets, transactions
- Media: images (polymorphic to users/sites/hardware/etc.)

## Alignment notes (highlights)

- Estimation currently links to `user` and `site`; projects can be initiated from an estimation (implicit linkage). Consider adding `project.estimation_id` if a strict link is required.
- Payments: ERD models milestone-based payments and splits; the MVP roadmap intentionally defers these in favor of simple upfront payments.
- Images: stored via a polymorphic design (`imageable_id`, `imageable_type`) to attach to multiple entities.

## Observed inconsistencies to resolve

- PaymentPlan linkage appears duplicated: ERD shows `payment_plan_id` on `projects` and also `project_id` on `payment_plans`. Prefer a single FK directional link (recommended: `payment_plans.project_id` with a unique index for 1:1).
- Resolved: decisions-summary aligned to Laravel (was referencing NestJS).
- Enum value sets used in OpenAPI should match DB enums (e.g., roles, statuses, payment_method). Create shared constants.

## Suggested constraints and indexes

- Foreign keys on all `_id` columns; unique constraint for 1:1 relationships (e.g., `payment_plans.project_id` UNIQUE).
- Indexes: `sites.user_id`, `user_appliances.site_id`, `project_hardware.project_id`, `payments.project_id`.
- Soft deletes for key entities (`projects`, `payments`, `hardware`) if business needs require recoverability.

