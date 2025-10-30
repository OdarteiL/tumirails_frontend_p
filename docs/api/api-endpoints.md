# API Endpoints Guide

The canonical API contract is defined in `docs/api/openapi.yaml` (OpenAPI 3.1).

## How to view

- Use a Swagger/OpenAPI viewer (e.g., Redocly, Swagger UI) and open the file `docs/api/openapi.yaml`.
- In VS Code, install an OpenAPI extension to preview the schema.

## Scope covered in the current spec

- Auth: register, login
- Sites: list, create
- Estimations: create; get recommendations for an estimation
- Payments: create payment for a project
- Appliances: list and get by id

## Notes

- The current spec is MVP-level and omits many entities in the ERD (e.g., projects CRUD, milestones, providers, installers, verification). Those should be added as we progress beyond MVP or as Sprint 0 tasks.
- Ensure response payloads and enums stay aligned with the database model (`tumi.dbml`).
