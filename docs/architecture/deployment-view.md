Deployment View

- Static assets served from CDN (CloudFront).
- Laravel services containerized and deployed to ECS Fargate.
- RDS (Aurora MySQL) for relational data; Redis (ElastiCache) for caching/queues.
- S3 for uploads; a write-once "ledger" stored as hashed objects (S3 + Dynamo index) for audit proofs.
- Payment gateway webhooks secured;
- Observability: tracing (X-Ray/Jaeger), metrics (Prometheus), logs (OpenSearch).