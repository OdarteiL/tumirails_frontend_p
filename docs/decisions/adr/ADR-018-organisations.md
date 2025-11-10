# ADR-018: Organisations Model

## Status
Approved

## Context
Users may act individually or as part of organisations (customers, installers, providers). We need a structure for ownership, membership, and role scoping.

## Decision
Introduce `organisations` entity with:
- Ownership (creator as primary owner)
- Membership table (user_id, organisation_id, role)
- Organisation types (ENUM: customer, installer, provider)

## Rationale
- Enables shared assets and permissions
- Prepares for team-level billing, project assignment, and reporting

## Consequences
- Access control must consider organisation context
- Migration path for individual-only users to attach to organisations later

## Follow-up Actions
- Add organisation relationships in ERD
- Extend auth middleware to load organisation context where needed
