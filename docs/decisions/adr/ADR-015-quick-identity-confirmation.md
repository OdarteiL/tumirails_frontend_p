# ADR-015: Quick Identity Confirmation (QIC)

## Status
Approved

## Context
To reduce fraud and ensure compliance, we need lightweight identity checks for all users and additional verification for installers and providers.

## Decision
Implement QIC with:
- Email and phone verification (OTP or link-based)
- Valid identity card capture (document upload or KYC API)
- Installer license and provider business certificate verification

## Rationale
- Balances friction with security for MVP
- Sets foundation for deeper KYC/AML post-MVP

## Consequences
- Requires secure file handling and storage policy
- Legal/privacy considerations documented in policies

## Follow-up Actions
- Define verification states in user profile
- Add endpoints and flows in OpenAPI
