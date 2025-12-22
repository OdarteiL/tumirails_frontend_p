# AI Assistance Context — Frontend (Angular)

Use this file as context whenever requesting AI assistance for frontend work.

## Architecture and conventions
- Framework: Angular 17+ (Standalone components preferred)
- State: Angular Signals for synchronous state (e.g. view models); RxJS for asynchronous events and streams. Use `toSignal` to bridge RxJS to Signals for templates.
- Routing: Angular Router with auth guards; lazy-load routes
- Services: Central HTTP client (Angular HttpClient) under `src/app/services/api.service.ts` and domain services under `src/app/services/` (e.g., `sites.service.ts`, `estimations.service.ts`)
- UI: Tailwind CSS; shared components in `src/app/components/shared/`
- Components: Smart (container) vs Dumb (presentational) separation; use Angular signals for reactive state

## Coding rules
- Functions small and single-purpose
- Adopt reusable components and services for common features and functionality
- Max two parameters per function; if more, pass an options object
- Prefer pure functions in services; side effects in components/services only
- Input count small (≤ 2 where possible); use objects for complex inputs
- Handle loading/error states explicitly; never swallow errors
- Use RxJS for complex event orchestration (debounce, switchMap, etc.) and Signals for derived state (`computed`).
- Prefer Signals over `BehaviorSubject` for component state.
- Type safety: Use TypeScript strictly; define interfaces for all data models
- Ensure no hardcoding of essential variables such as URLs, etc

## ICON Library
- Use lucide icons library.
- Use svg icons only when lucide does not have the icon.
- Refer to https://lucide.dev/guide/packages/lucide-angular documentation for usage.

## API usage
- All HTTP calls through centralized HTTP service; include auth token via interceptors; map server errors to UI-friendly messages
- Keep OpenAPI as the contract; generate TypeScript interfaces from schema

## Testing
- Unit tests for components and services (Jasmine/Karma)
- Cypress (post-MVP) for basic E2E happy path

## Prompts for AI (frontend)
- Always include: this AI_CONTEXT.md, the file path(s), goal, constraints, acceptance criteria
- Provide current component/service snippet and desired behavior
- Ask for unit tests and explicit accessibility considerations (keyboard focus, ARIA)
