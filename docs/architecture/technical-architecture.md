# Technical Architecture

## System Overview

The Tumi Solar Configurator follows a modern web application architecture with clear separation between frontend and backend services.

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   Vue.js SPA    │    │   Laravel API   │    │   Database      │
│   (Frontend)    │◄──►│   (Backend)     │◄──►│   (MySQL)       │
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

## Frontend Architecture (Vue.js)

### Directory Structure
```
frontend/
├── src/
│   ├── components/
│   │   ├── common/
│   │   ├── forms/
│   │   └── charts/
│   ├── views/
│   │   ├── auth/
│   │   ├── dashboard/
│   │   ├── sites/
│   │   ├── projects/
│   │   └── admin/
│   ├── router/
│   ├── store/
│   │   ├── modules/
│   │   │   ├── auth.js
│   │   │   ├── sites.js
│   │   │   ├── projects.js
│   │   │   └── payments.js
│   │   └── index.js
│   ├── services/
│   │   ├── api.js
│   │   ├── auth.js
│   │   └── payment.js
│   └── utils/
├── public/
└── package.json
```

### Key Technologies
- **Vue 3**: Composition API for better code organization
- **Vue Router**: Client-side routing with guards
- **Vuex/Pinia**: State management
- **Axios**: HTTP client for API calls
- **Tailwind CSS**: Utility-first CSS framework
- **Chart.js**: Data visualization for analytics

### State Management
```javascript
// Store modules structure
store/
├── auth.js      // User authentication state
├── sites.js     // Site management
├── projects.js  // Project tracking
├── hardware.js  // Hardware catalog
└── payments.js  // Payment processing
```

### Component Architecture
- **Atomic Design**: Atoms → Molecules → Organisms → Templates → Pages
- **Reusable Components**: Form inputs, data tables, charts
- **Smart/Dumb Components**: Container vs Presentational

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
1. User login → API returns JWT token
2. Frontend stores token in localStorage
3. All API requests include Authorization header
4. Backend validates token on protected routes

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
- Database query optimization
- Eager loading relationships
- Caching (Redis)
- Queue jobs for heavy tasks
- API response caching

### Frontend Optimization
- Code splitting
- Lazy loading routes
- Image optimization
- Bundle size optimization
- Progressive Web App features

## Deployment Architecture

### Development Environment
```
Docker Compose:
├── Laravel (PHP-FPM + Nginx)
├── Vue.js (Development server)
├── MySQL
├── Redis
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
- Unit tests (Jest)
- Component tests (Vue Test Utils)
- E2E tests (Cypress)
- Visual regression tests

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
