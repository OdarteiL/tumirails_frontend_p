# AI Assistance Guide

When requesting AI help, include the relevant context file and specifics about your task.

## What to include in your prompt
- The appropriate AI context file:
  - Backend: `backend/AI_CONTEXT.md`
  - Frontend: `frontend/AI_CONTEXT.md`
- File paths and code snippets you’re changing
- Goal, constraints, and acceptance criteria
- Sample inputs/outputs and failure modes
- Ask for tests (unit/feature) and edge cases

## Why this matters
- Ensures code follows our architecture and style
- Reduces rework from vague prompts
- Keeps API and data models aligned with docs

## Examples
- "Using backend/AI_CONTEXT.md, create CreateEstimationAction.php to compute total_kW and daily_kWh with tests; accept at most two parameters (DTO)."
- "Using frontend/AI_CONTEXT.md, add a sites store with addSite() and loadSites(); ensure API calls go through services/api.ts and show error toasts on failure."
