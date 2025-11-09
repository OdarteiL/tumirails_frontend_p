# ADR-013: Estimation Feature (MVP)

## Status
Approved

## Context
Users must estimate site energy needs using either detailed appliance selection or historical spend patterns.

## Decision
Implement an estimation module that supports:
- Power estimation via appliance selection (wattage × hours × count)
- Alternative estimation via historical spend averages (converted to kWh proxy)

## Rationale
- Enables users with different levels of detail to estimate reliably
- Sets the foundation for recommendations and sizing

## Consequences
- Requires clear data model for appliances and usage profiles
- Validation and UX flows to avoid under/over-estimation

## Follow-up Actions
- Define payloads in OpenAPI and entities in ERD
- Provide default appliance catalog and tunable factors
