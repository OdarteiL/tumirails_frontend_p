# MVP Definition and Roadmap

## MVP Core Features (End-to-End Flow)

The MVP focuses on the essential workflow: Customer creates estimate → Gets recommendations → Initiates project → Installer completes → Customer pays.

### 1. User Management (MVP)
- User registration/login (Customer, Provider, Installer, Admin)
- Basic profile management
- Role-based access control

### 2. Site Management (MVP)
- Customers can create sites with basic info (name, address, coordinates)
- Single site per customer for MVP

### 3. Energy Estimation (MVP)
- Admin-managed appliance catalog
- Customers add appliances to their site
- Basic energy calculation (total kW, daily kWh)
- Simple cost estimation

### 4. Hardware Management (MVP)
- Admin-managed hardware types (Panel, Inverter, Battery)
- Providers can add hardware with basic specs and pricing
- Simple hardware recommendation based on energy needs

### 5. Project Workflow (MVP)
- Customer initiates project from estimation
- Simple project status tracking (Initiated → In Progress → Completed)
- Basic installer assignment (manual by admin)

### 6. Payment System (MVP)
- Simple upfront payment model
- Single payment gateway integration (Paystack)
- Basic payment tracking

### 7. Basic Admin Panel (MVP)
- User management
- Appliance/hardware type management
- Project oversight

## MVP Database Tables (Essential)

### Core Tables
- `users` - All user types
- `sites` - Customer properties
- `appliances` - Admin-managed appliance catalog
- `user_appliances` - Customer's appliances per site
- `categories` - Appliance categories
- `hardware_types` - Panel, Inverter, Battery, etc.
- `providers` - Provider profiles
- `hardware` - Provider's hardware catalog
- `installers` - Installer profiles
- `estimations` - Energy calculations
- `recommended_hardware` - Hardware suggestions
- `projects` - Installation projects
- `project_hardware` - Hardware assigned to projects
- `payments` - Payment records

### Simplified for MVP
- Remove complex payment plans/milestones
- Remove verification system
- Remove image management
- Remove wallet/transaction splitting
- Remove advanced payment splitting

## Post-MVP Features (Phase 2+)

### Phase 2: Enhanced User Experience
- Image Management System
- Advanced Estimation
- Installer Marketplace

### Phase 3: Quality Assurance
- Verification System
- Advanced Project Management

### Phase 4: Financial Features
- Advanced Payment System
- Wallet System

### Phase 5: Business Intelligence
- Analytics Dashboard
- Reporting System

### Phase 6: Advanced Features
- AI/ML Integration
- Mobile Applications
- Integration Ecosystem

## MVP Development Priority

### Week 1-2: Foundation
- Laravel project setup
- Vue.js frontend setup
- Database migrations
- Authentication system

### Week 3-4: Core Models
- User management
- Site management
- Appliance catalog
- Basic admin panel

### Week 5-6: Estimation Engine
- Energy calculation logic
- Hardware recommendation
- Estimation interface

### Week 7-8: Project Management
- Project creation
- Basic workflow
- Installer assignment

### Week 9-10: Payment Integration
- Payment gateway setup
- Payment processing
- Payment tracking

### Week 11-12: Testing & Polish
- End-to-end testing
- UI/UX refinements
- Bug fixes
- Deployment preparation

## Success Metrics for MVP

- Customer can complete full estimation process
- Hardware recommendations are generated
- Projects can be created and tracked
- Payments can be processed successfully
- All user roles can perform basic functions
- System handles 100+ concurrent users
- 95% uptime during testing period
