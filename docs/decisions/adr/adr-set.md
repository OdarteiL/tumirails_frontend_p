| ID      | Title                                       | Description                                                                            | Status   |
| ------- | ------------------------------------------- | -------------------------------------------------------------------------------------- | -------- |
| [ADR-001](ADR-001-use-laravel-11-backend-api.md) | Use Laravel 11 for Backend API              | Choose Laravel for rapid backend API and ORM support.                                  | Approved |
| [ADR-002](ADR-002-use-angular-frontend.md) | Use Angular for Frontend                    | Standardize UI framework for consistency, performance, and team proficiency. | Approved |
| ADR-003 | Deployment | Decision on deployment postponed for now                                    | Postponed |
| ADR-004 | Use Escrow for Payments                | Ensure trusted transactions between users and installers.                              | Postponed |
| ADR-005 | Modular API Structure                       | Build API around independent functional domains (Estimation, Hardware, Installations). | Pending |
| ADR-006 | Implement Caching Layer (Redis)             | Speed up estimation queries and page loads.                                            | Proposed |
| ADR-007 | Use Terraform for Infrastructure Management | Standardize IaC deployment and version control.                                        | Pending |
| ADR-008 | JWT + Role-Based Access Control             | Secure multi-role interactions (user, provider, installer, regulator).                 | Pending |
| ADR-009 | Use AWS Aurora Postgres for DB                 | Ensure relational integrity with scalability and backups.                              | Pending |
| ADR-010 | CI/CD with GitHub Actions + CodePipeline    | Enable automated deployment and testing per branch.                                    | Pending |
| [ADR-011](ADR-011-laravel-layering-controllers-services-actions.md) | Laravel Layering: Controllers→Services→Actions | Keep controllers thin, isolate orchestration and units of work, improve testability.   | Approved |
| [ADR-012](ADR-012-branching-strategy-develop-staging-production.md) | Branching Strategy: develop→staging→production | Feature branches to develop; QA on staging; releases from staging to production.       | Approved |
| [ADR-013](ADR-013-estimation-feature.md) | Estimation feature | Estimate power for entire site using power consumption or average of amount spent per period. Estimate section by allowing user to pick appliances. | Approved |
| [ADR-014](ADR-014-payment-feature.md) | Payment feature | Stick to proposed payment (Payment plan, milestones and splits) with user being to see for what they have paid/which percentage payment made. | Approved |
| [ADR-015](ADR-015-quick-identity-confirmation.md) | Quick Identity Confirmation/Check (QIC)     | Verify email, phone number, and a valid identity card for all users; verify installer license and provider business certificate. | Approved |
| [ADR-016](ADR-016-hardware-type-attributes.md) | Hardware Type | Hardware type related attributes should now be part of hardware entity. | Approved |
| [ADR-017](ADR-017-full-recommendation-system.md) | Full Recommendation System | Have a simple recommendation feature for MVP and full AI-powered recommendation system POST MVP. | Approved |
| [ADR-018](ADR-018-organisations.md) | Organisations | User can own or be part of organisations. Organisations can be customers, installers or providers. | Approved |
| [ADR-019](ADR-019-site-visitation-scheduling.md) | Site Visitation & Scheduling                | Customers/installers indicate availability; book appointments; match customer with chosen installer based on availability; pick location on a map. | Approved |
