# ADR-017: Recommendation System Evolution

## Status
Approved

## Context
Users benefit from guidance selecting hardware. MVP requires simple rule-based suggestions; post-MVP should leverage richer data and potentially ML models.

## Decision
Two-phase approach:
- MVP: Deterministic rules (estimated kWh, budget, hardware availability)
- Post-MVP: AI-assisted recommendations (historical performance, optimization models)

## Rationale
- Delivers immediate value without blocking on data/ML maturity
- Creates natural upgrade path

## Consequences
- Need abstraction layer for recommendation service to avoid refactor later
- Data collection for post-MVP must start early (usage, selections)

## Follow-up Actions
- Define interface for RecommendationService
- Log estimation inputs and chosen hardware for future model training
