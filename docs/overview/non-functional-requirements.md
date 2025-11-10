# Non-Functional Requirements (NFRs)

## Availability & Reliability
- Target 99.5% during pilot; scale to 99.9% post-MVP.
- Error budgets and simple SLOs for API latency and uptime.

## Performance
- P95 API latency under 300ms for standard CRUD; under 1s for estimation.
- Use Redis for caching and queues.

## Scalability
- Horizontal scale on AWS ECS Fargate; RDS with read replicas (post-MVP if needed).
- CDN (CloudFront) for SPA and images.

## Security
- JWT (Sanctum) auth; RBAC.
- Encrypted secrets; secure payment webhooks (signature verification).
- PII handling and least-privilege data access.

## Observability
- Centralized logs; metrics and traces (CloudWatch/Prometheus/Sentry).

## Data Management
- Foreign key constraints; indexing on *_id columns.
- Soft deletes on key entities if recovery is required.
