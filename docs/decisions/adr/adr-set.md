| ID      | Title                                       | Description                                                                            | Status   |
| ------- | ------------------------------------------- | -------------------------------------------------------------------------------------- | -------- |
| ADR-001 | Use Laravel 11 for Backend API              | Choose Laravel for rapid backend API and ORM support.                                  | Approved |
| ADR-002 | Use Vue 3 + Pinia + Tailwind for Frontend   | Standardize UI framework for consistency and performance.                              | Approved |
| ADR-003 | Deploy on AWS ECS Fargate                   | Adopt containerized infrastructure for scalability.                                    | Approved |
| ADR-004 | Use MoMo Escrow for Payments                | Ensure trusted transactions between users and installers.                              | Approved |
| ADR-005 | Modular API Structure                       | Build API around independent functional domains (Estimation, Hardware, Installations). | Approved |
| ADR-006 | Implement Caching Layer (Redis)             | Speed up estimation queries and page loads.                                            | Proposed |
| ADR-007 | Use Terraform for Infrastructure Management | Standardize IaC deployment and version control.                                        | Approved |
| ADR-008 | JWT + Role-Based Access Control             | Secure multi-role interactions (user, provider, installer, regulator).                 | Approved |
| ADR-009 | Use AWS Aurora MySQL for DB                 | Ensure relational integrity with scalability and backups.                              | Approved |
| ADR-010 | CI/CD with GitHub Actions + CodePipeline    | Enable automated deployment and testing per branch.                                    | Proposed |
| ADR-011 | Laravel Layering: Controllers→Services→Actions | Keep controllers thin, isolate orchestration and units of work, improve testability.   | Approved |
| ADR-012 | Branching Strategy: develop→staging→production | Feature branches to develop; QA on staging; releases from staging to production.       | Approved |
