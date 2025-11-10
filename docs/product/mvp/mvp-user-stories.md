# MVP User Stories (Subset)

This subset lists only the stories required to complete the MVP defined in `mvp-roadmap.md`.

## Customer
- As a customer, I want to register and log in so I can access my dashboard.
- As a customer, I want to create a site with name, address, and coordinates so I can estimate energy needs. (MVP: single site)
- As a customer, I want to select appliances from a catalog and provide basic usage so I can estimate my energy needs.
- As a customer, I want to view my total kW, daily kWh, and estimated monthly cost so I can understand my requirements.
- As a customer, I want to see recommended hardware (panel, inverter, battery) so I know what equipment to consider.
- As a customer, I want to initiate a project from an estimation so installation can proceed.
- As a customer, I want to make an upfront payment for my project so I can confirm the order.

## Provider
- As a provider, I want to add hardware with basic specs and pricing so it can be recommended.

## Installer
- As an installer, I want a basic profile (name, coverage area) so admins can assign me to projects. (Assignment manual in MVP)

## Admin
- As an admin, I want to manage users and roles so platform access is controlled.
- As an admin, I want to manage appliance categories and hardware types so catalogs stay organized.
- As an admin, I want to assign installers to projects so execution can begin.

## Out of scope for MVP (deferred)
- Milestone-based payments, payment splitting, wallets.
- Verification workflows and image/document management.
- Installer marketplace, bidding, advanced scheduling.

## Sprint assignments (2 × 1-week sprints)

| Jira Key | Story (summary) | Sprint |
|---------:|-----------------|:------:|
| TUMI-1   | User Registration & Login | Sprint 1 |
| TUMI-2   | Create Site (single) | Sprint 1 |
| TUMI-3   | Appliance Catalog (seed + list) | Sprint 1 |
| TUMI-4   | Add User Appliances | Sprint 1 |
| TUMI-5   | Create Estimation (kW/kWh/monthly cost) | Sprint 1 |
| TUMI-6   | Get Recommendations (hardware) | Sprint 1 |
| TUMI-7   | Seed Data & Demo | Sprint 1 |
| TUMI-8   | Create Project from Estimation | Sprint 2 |
| TUMI-9   | Manual Installer Assignment (admin) | Sprint 2 |
| TUMI-10  | Upfront Payment (gateway integration) | Sprint 2 |
| TUMI-11  | Manage Hardware Types & Categories (admin) | Sprint 2 |
| TUMI-12  | Basic User Management (admin) | Sprint 2 |

Each story in Sprint 1 is sized and prioritized to deliver an end-to-end estimation → recommendation flow. Sprint 2 focuses on project creation, assignment, and payment to complete the MVP business flow.
