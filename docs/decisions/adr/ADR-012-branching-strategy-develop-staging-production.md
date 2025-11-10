# ADR-012: Branching Strategy — develop → staging → production

Date: 2025-10-30
Status: Approved
Owner: Engineering Lead

## 1. Context
We need a simple, explicit branching model to manage trunk integration, QA, and releases while enabling parallel feature work.

## 2. Decision
Adopt a three-branch flow with PR gates:
- Main branches: `develop`, `staging`, `production`
- Feature branches: `feature/<short-summary>` off `develop`; PR → `develop`
- Testing: Merge `develop` → PR → `staging` for QA and UAT
- Release: After successful testing, PR `staging` → `production`
- Hotfixes: `hotfix/<issue>` from `production`; PR back to `production` then back-merge to `develop` and `staging`

Policies
- All merges via PR with code review and passing checks
- Protect `staging` and `production` (required reviews, status checks)
- Tag releases on `production` (e.g., `vX.Y.Z`)

## 3. Alternatives Considered
| Option | Pros | Cons |
|-------|------|------|
| GitFlow (full) | Formal release branches | Heavy for small team; more overhead |
| Trunk-only | Fast iteration | Risky for QA; fewer isolation points |

## 4. Consequences
- + Clear environments alignment: develop (integration), staging (QA), production (live)
- + Predictable release cadence and hotfix path
- − Requires discipline to back-merge hotfixes

## 5. Related Decisions
- TRR: Release and testing cadence
- ADR-005 Modular API Structure
