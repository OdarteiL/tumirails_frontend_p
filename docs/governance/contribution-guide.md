# Contribution Guide (Docs)

## Proposing changes
- Open a PR with a concise description of intent and scope.
- Update `docs/README.md` navigation if you add or move files.
- For architectural decisions, add an ADR under `docs/decisions/adr/` using the template.
- For cross-team checkpoints, capture outcomes in a TRR under `docs/governance/trr/`.

## Review expectations
- Keep docs atomic and scoped; prefer small PRs.
- Link related issues/ADRs/TRRs.
- Ensure links resolve and diagrams render.

## Versioning
- Use semantic headings and dates in TRRs/ADRs.
- Include a short changelog entry if you change scope or architecture.

## Branching workflow (overview)
- Feature branches from `develop` using `feature/<short-summary>`
- PRs into `develop` with review + passing checks
- QA via PR from `develop` to `staging`
- Release via PR from `staging` to `production`; tag release
- Hotfixes from `production` then back-merge to `develop` and `staging`
See `docs/governance/branching-strategy.md` and ADR-012 for details.
