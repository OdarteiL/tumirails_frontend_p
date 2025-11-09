# ADR-019: Site Visitation & Scheduling

## Status
Approved

## Context
Successful installations rely on coordinated site visits. Customers need to request visits; installers must provide availability; both parties benefit from scheduling transparency.

## Decision
Implement a visitation module with:
- Availability windows (installer + optionally customer)
- Appointment requests and confirmation workflow
- Matching logic using chosen installer’s availability
- Location selection via map interface

## Rationale
- Improves installation planning and reduces delays
- Foundation for future routing/optimization

## Consequences
- Requires calendar/availability data structures
- Mapping integration (geocoding) needed

## Follow-up Actions
- Define appointment and availability entities in ERD
- Add endpoints in OpenAPI for availability, booking, confirmation
