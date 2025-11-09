# ADR-016: Hardware Type Attributes on Hardware Entity

## Status
Approved

## Context
Initial modeling placed type-specific attributes externally. This complicates queries and increases joins.

## Decision
Move hardware type attributes into the `hardware` entity, using typed fields or JSON where practical, with validation ensuring consistency per type.

## Rationale
- Simplifies reads and recommendations
- Fewer joins improve performance for MVP

## Consequences
- Migrations must handle type evolution carefully
- Validation rules per hardware type required

## Follow-up Actions
- Update ERD and schema with consolidated attributes
- Document mapping and constraints in database schema summary
