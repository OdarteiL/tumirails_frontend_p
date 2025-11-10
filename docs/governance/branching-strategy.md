# Branching Strategy — develop → staging → production

We use three protected branches and PR gates to manage integration, testing, and releases.

## Branches
- develop: integration branch; features are merged here first
- staging: QA/UAT branch; receives PRs from develop
- production: live branch; receives PRs from staging

## Workflow
1. Branch off develop: `feature/<short-summary>`
2. Open PR to develop; require review + passing checks
3. When ready for QA, open PR from develop to staging
4. After testing passes, open PR from staging to production
5. Tag production after merge (e.g., vX.Y.Z)

## Hotfixes
- Create `hotfix/<issue>` from production
- PR to production, then back-merge to develop and staging to keep history aligned

## Policies
- All merges via PR; no direct pushes to staging/production
- Required reviews (at least one) and passing CI checks
- Keep PRs small and focused; link issues/tickets

See ADR-012 for rationale and alternatives, and the TRR for approval history.
