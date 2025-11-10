# Technical Architecture

## System Overview

The Tumi Solar Configurator follows a modern web application architecture with clear separation between frontend and backend services.

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│  Angular SPA    │    │   Laravel API   │    │   Database      │
│   (Frontend)    │◄──►│   (Backend)     │◄──►│ (Postgres/MySQL)│
└─────────────────┘    └─────────────────┘    └─────────────────┘
         │                       │                       │
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   CDN/Storage   │    │  Payment APIs   │    │   File Storage  │
│   (Images)      │    │  (Paystack)     │    │   (AWS S3)      │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## Backend Architecture (Laravel)

### Directory Structure
```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── SiteController.php
│   │   │   │   ├── EstimationController.php
│   │   │   │   ├── ProjectController.php
│   │   │   │   └── PaymentController.php
│   │   │   └── Admin/
│   │   ├── Middleware/
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Site.php
│   │   ├── Appliance.php
│   │   ├── Hardware.php
│   │   ├── Project.php
│   │   └── Payment.php
│   ├── Services/
│   │   ├── EstimationService.php
│   │   ├── RecommendationService.php
│   │   └── PaymentService.php
│   └── Jobs/
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   ├── api.php
│   └── web.php
└── config/
```

### Key Components

#### Models & Relationships
- **User Model**: Polymorphic relationships with Provider, Installer, Verifier
- **Site Model**: Belongs to User, has many UserAppliances
- **Project Model**: Central hub connecting Customer, Installer, Site, Hardware
- **Payment Model**: Polymorphic paymentable (PaymentPlan/Milestone)

#### Services Layer
- **EstimationService**: Energy calculation logic
- **RecommendationService**: Hardware recommendation algorithms
- **PaymentService**: Payment processing and gateway integration
- **NotificationService**: Email/SMS notifications

#### API Controllers
- RESTful API design
- Resource-based routing
- Consistent JSON responses
- Proper HTTP status codes

### Authentication & Authorization
- Laravel Sanctum for API authentication
- Role-based access control (RBAC)
- Middleware for route protection
- Token-based authentication for SPA

### Database Design
- MySQL/PostgreSQL for relational data
- Proper indexing for performance
- Foreign key constraints
- Soft deletes for important records

## Frontend Architecture (Angular)

### Directory Structure
```
frontend/
├── src/
│   ├── app/
│   │   ├── core/               # Singleton services (api, auth, config)
│   │   ├── shared/             # Reusable components, pipes, directives
│   │   ├── features/
│   │   │   ├── auth/
│   │   │   ├── dashboard/
│   │   │   ├── sites/
│   │   │   ├── projects/
│   │   │   └── payments/
│   │   ├── state/              # NgRx slices or Signal-based stores (future)
│   │   ├── routing/            # App routing module(s)
│   │   └── app.module.ts
│   ├── environments/           # environment.ts files
│   └── main.ts
├── index.html
├── tailwind.config.js
└── package.json
```

### Key Technologies
- **Angular 17+**: Component & module architecture; Signals (optional) for reactive state
- **Angular Router**: Client-side navigation with guards & lazy loading
- **RxJS**: Reactive programming for async flows
- **NgRx (post-MVP optional)**: Predictable state management if complexity grows
- **HTTP Client (Angular)**: Built-in typed API access; interceptors for auth
- **Tailwind CSS**: Utility-first styling
- **Charting (ngx-charts / Chart.js)**: Data visualization for analytics

### State Management
Initial MVP will rely on component + service state (RxJS Subjects / Signals). Introduce NgRx only when:
- Cross-feature state coordination becomes complex
- Time-travel debugging or advanced caching is needed

Structure (future NgRx example):
```
state/
├── auth/
├── sites/
├── projects/
├── hardware/
└── payments/
```

### Component Architecture
- **Feature Modules**: Encapsulate domain (e.g., SitesModule, ProjectsModule)
- **Shared Module**: Cross-cutting UI components and utilities
- **Core Module**: Singleton services (AuthService, ApiService)
- **Container vs Presentational**: Keep business logic in services, presentation in components

## API Design

### RESTful Principles
- Resource-based URLs
- HTTP verbs for actions
- Consistent response format
- Proper status codes

### Response Format
```javascript
// Success
{
  "success": true,
  "data": {},
  "message": "Operation successful"
}

// Error
{
  "success": false,
  "error": "Error message",
  "errors": {}
}
```

### Authentication Flow
1. User login → API returns Sanctum token (or session cookie for SPA)
2. Frontend stores token securely (in memory or localStorage if acceptable)
3. HTTP interceptor attaches Authorization header (Bearer <token>) or relies on cookie
4. Backend validates token via Sanctum on protected routes

### Backend Layering (Controllers → Services → Actions)
- Controllers: Thin adapters, validate input, delegate work only
- Services: Orchestrate application use-cases, transactions, and cross-entity operations
- Actions: Single-responsibility units of work (side-effecting or pure), reusable and testable

Recommended conventions:
- Services end with `Service` (e.g., `EstimationService`)
- Actions end with `Action` and use imperative naming (e.g., `CalculateEstimationAction`)
- Do not place business logic in controllers or models; keep Eloquent models lean

## Payment Integration

### Gateway Architecture
```
Frontend → Laravel PaymentService → Gateway API → Webhook → Laravel
```

### Supported Gateways
- **Paystack**: Primary for Ghana/Nigeria
- **Flutterwave**: Backup/alternative
- **Stripe**: International payments

### Payment Flow
1. Customer initiates payment
2. Frontend calls Laravel payment endpoint
3. Laravel creates payment record
4. Gateway processes payment
5. Webhook confirms payment status
6. System updates payment status

## File Storage

### Image Management
- **Local Development**: Laravel storage
- **Production**: AWS S3 bucket
- **CDN**: CloudFront for fast delivery
- **Processing**: Image optimization and resizing

### File Types
- User profile pictures
- Site documentation photos
- Hardware product images
- Installation verification photos

## Security Measures

### Backend Security
- Input validation and sanitization
- SQL injection prevention (Eloquent ORM)
- CSRF protection
- Rate limiting
- Secure headers

### Frontend Security
- XSS prevention
- Secure token storage
- Input validation
- HTTPS enforcement

### Data Protection
- Password hashing (bcrypt)
- Sensitive data encryption
- PII data handling
- GDPR compliance considerations

## Performance Optimization

### Backend Optimization
- Database query optimization & eager loading
- Layered architecture (Controllers → Services → Actions) ensures small units and testability
- Caching (Redis) for estimation & recommendations
- Queue jobs for heavy tasks (reports, notifications)
- API response caching (headers / application layer)

### Frontend Optimization
- Route-based code splitting (lazy-loaded Angular modules)
- Preloading strategy where beneficial (e.g., after auth)
- Image optimization & responsive sources
- Tailwind purge to reduce CSS size
- Progressive Web App features (offline estimation entry, caching)

## Deployment Architecture

### Development Environment
```
Docker Compose:
├── Laravel (PHP-FPM + Nginx) / Sanctum
├── Angular (ng serve dev server)
├── Postgres (Primary) or MySQL (alternate)
├── Redis (Cache/Queue)
└── MailHog (Email testing)
```

### Production Environment
```
AWS Infrastructure:
├── EC2 (Laravel API)
├── RDS (MySQL)
├── S3 (File storage)
├── CloudFront (CDN)
├── Route 53 (DNS)
└── Load Balancer
```

## Monitoring & Logging

### Application Monitoring
- Laravel Telescope (Development)
- Application logs
- Error tracking (Sentry)
- Performance monitoring

### Infrastructure Monitoring
- Server metrics
- Database performance
- API response times
- Uptime monitoring

## Testing Strategy

### Backend Testing
- Unit tests (PHPUnit)
- Feature tests
- API endpoint tests
- Database tests

### Frontend Testing
- Unit/component tests (Jest + Angular Testing Library)
- Integration tests (Jest + HTTP mocks)
- E2E tests (Cypress)
- Visual regression tests (Percy/Chromatic optional)

## Scalability Considerations

### Horizontal Scaling
- Load balancer for multiple API servers
- Database read replicas
- CDN for static assets
- Queue workers scaling

### Vertical Scaling
- Server resource optimization
- Database performance tuning
- Caching strategies
- Code optimization
