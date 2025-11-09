# ADR-014: Payment Feature (Milestone-Based Plan)

## Status
Approved

## Context
Projects involve multiple phases; users need visibility into payment progress and what each portion covers. Milestone-based payment plans with splits improve transparency and cash flow management.

## Decision
Implement payment plans with:
- Defined milestones (e.g., Deposit, Hardware Procurement, Installation, Finalization)
- Percentage splits per milestone
- Tracking of paid percentage and outstanding balance

## Rationale
- Increases trust by mapping payments to tangible progress
- Facilitates future escrow integration post-MVP

## Consequences
- Data model must support milestone definitions and payment records
- Requires alignment with future escrow service (ADR-004, postponed)

## Follow-up Actions
- Model PaymentPlan, Milestone entities in ERD
- Integrate gateway (Paystack primary) API flows in OpenAPI
