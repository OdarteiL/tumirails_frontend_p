# AI Assistance Context — Frontend (Vue 3)

Use this file as context whenever requesting AI assistance for frontend work.

## Architecture and conventions
- Framework: Vue 3 (Composition API; `<script setup>` preferred)
- State: Pinia stores per domain; keep stores small; avoid God-stores
- Routing: Vue Router with auth guards; lazy-load routes
- Services: Central API client (Axios) under `src/services/api.ts` and domain clients under `src/services/` (e.g., `sites.ts`, `estimations.ts`)
- UI: Tailwind CSS; shared components in `src/components/common/`
- Components: Smart (container) vs Dumb (presentational) separation; composables in `src/composables/`

## Coding rules
- Functions small and single-purpose
- Max two parameters per function; if more, pass an options object
- Prefer pure functions in composables; side effects in components/services only
- Prop count small (≤ 2 where possible); use objects for complex props
- Handle loading/error states explicitly; never swallow errors
- Use async/await with try/catch; centralize error handling
- Type safety: If TS not available, use JSDoc typedefs and runtime validation

## API usage
- All HTTP calls through centralized API client; include auth token; map server errors to UI-friendly messages
- Keep OpenAPI as the contract; generate types later if TS is adopted

## Testing
- Unit tests for components and composables (e.g., Vitest/Jest)
- Cypress (post-MVP) for basic E2E happy path

## Prompts for AI (frontend)
- Always include: this AI_CONTEXT.md, the file path(s), goal, constraints, acceptance criteria
- Provide current component/store snippet and desired behavior
- Ask for unit tests and explicit accessibility considerations (keyboard focus, ARIA)
