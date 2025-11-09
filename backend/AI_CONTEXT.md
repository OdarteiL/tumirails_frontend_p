# AI Assistance Context — Backend (Laravel)

Use this file as context whenever requesting AI assistance for backend work.

## Architecture and layering
- Pattern: Controllers → Services → Actions
  - Controllers: Thin; HTTP concerns only (auth, validation via Form Requests, resource responses)
  - Services: Orchestrate one use-case; handle transactions and cross-entity coordination
  - Actions: Single, small unit of work (e.g., CreateEstimation, RecommendHardware); test in isolation
- Validation: Laravel Form Request classes; sanitize and validate input at boundaries
- Data: Prefer DTOs/value objects to pass data between layers; avoid passing raw arrays
- Persistence: Eloquent ORM; eager-load relationships; keep queries in services/actions
- Responses: Use API Resources/Resource Collections; never return models directly to clients

## Coding rules
- Functions should be small and do one thing
- Max two parameters per function; if more required, create a DTO or options object
- Use typed properties and return types; enable strict types where practical
- Avoid static state; prefer dependency injection
- Handle errors explicitly; avoid silent failures; throw domain exceptions and map to HTTP responses
- Wrap multi-write operations in DB::transaction()
- Logging: Log unexpected exceptions; avoid leaking PII
- Respect RESTful API conventions

## Naming and structure
- Services: App/Services/* (e.g., EstimationService)
- Actions: App/Actions/<Domain>/<ActionName>Action.php (e.g., Actions/Estimation/CreateEstimationAction.php)
- Requests: App/Http/Requests/*
- Resources: App/Http/Resources/*
- Controllers: App/Http/Controllers/Api/*

## Response Format
```javascript
// Success
{
  "success": true,
  "data": {},
  "message": "Operation successful"
}

// Error
{
  "success": false,
  "error": "Error message",
  "errors": {}
}
```

## Testing
- Unit tests for Actions and domain logic (PHPUnit)
- Feature tests for Controllers and happy paths
- Use factories and seeders for repeatable test data

## API contract
- Keep OpenAPI (docs/api/openapi.yaml) updated with endpoints, request/response schemas, and enums
- Align enums with DB schema (roles, statuses, payment_method)

## Prompts for AI (backend)
- Always include: this AI_CONTEXT.md, the file path(s), goal, constraints, acceptance criteria
- Provide sample inputs/outputs and failure modes
- Ask for unit tests and edge cases; prefer incremental diffs to large rewrites
