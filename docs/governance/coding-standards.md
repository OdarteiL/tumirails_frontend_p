# Coding & Documentation Standards

## Documentation
- One canonical index: `docs/README.md`.
- Use clear, skimmable headings and short paragraphs.
- Co-locate diagrams with their topic folders.

## API Contracts
- Contract-first via OpenAPI (`docs/api/openapi.yaml`).
- Keep enums and error formats consistent; update clients as needed.

## Data Model
- DBML is the source for ERD (`docs/architecture/data-models/tumi.dbml`).
- Enforce FKs and indexes in migrations.

## ADRs & TRRs
- ADRs: one decision per file; status lifecycle (Proposed → Approved → Superseded).
- TRRs: use the template; include participants, decisions, risks, outcomes.
