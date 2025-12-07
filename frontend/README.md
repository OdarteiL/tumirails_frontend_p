# Tumi Configurator - Frontend

[![CI Pipeline](https://github.com/tumirailsdotcom/tumi_configurator/workflows/CI%20Pipeline/badge.svg)](https://github.com/tumirailsdotcom/tumi_configurator/actions)

Angular 20 frontend application for the Tumi Solar Configurator platform.

## Features

- **User Authentication** - Register, login, logout functionality
- **Reactive Forms** - Form validation with Angular reactive forms
- **State Management** - Angular signals for reactive state
- **Modern Styling** - Tailwind CSS for responsive design
- **Route Guards** - Protected routes requiring authentication
- **Type Safety** - TypeScript for enhanced development experience

## Tech Stack

- **Angular** 20.x
- **TypeScript** 5.9
- **Tailwind CSS** - Utility-first CSS framework
- **RxJS** - Reactive programming
- **Angular Router** - Client-side routing

## Getting Started

### Prerequisites

- Node.js 20.x or higher
- npm 10.x or higher

### Installation

1. Install dependencies:
```bash
npm install
```

2. Start the development server:
```bash
npm start
# or
ng serve
```

3. Navigate to `http://localhost:4200`

The application will automatically reload when you make changes to the source files.

## Development

### Available Scripts

```bash
# Start development server
npm start

# Build for production
npm run build

# Run linter
npm run lint

# Fix linting issues
npm run lint -- --fix

# Run unit tests
npm test

# Watch tests
npm run test -- --watch
```

### Code Style

This project uses ESLint with Angular-specific rules:

```bash
# Check code style
npm run lint

# Auto-fix linting issues
npm run lint -- --fix
```

## Application Structure

```
src/
├── app/
│   ├── components/     # Reusable UI components
│   ├── pages/          # Route-level components
│   ├── services/       # Business logic and API calls
│   ├── guards/         # Route guards
│   └── models/         # TypeScript interfaces/types
├── assets/             # Static assets
└── styles/             # Global styles
```

## Routes

- `/` - Landing page
- `/login` - User login page
- `/register` - User registration page
- `/dashboard` - Protected dashboard (requires authentication)

## API Integration

The frontend communicates with the Laravel backend API at `http://localhost:8000/api`.

### API Documentation

For complete API endpoint documentation, refer to the OpenAPI specification:

📄 [`/docs/api/openapi.yaml`](../docs/api/openapi.yaml)

The OpenAPI spec includes:
- All available endpoints
- Request/response schemas
- Authentication requirements
- Example requests and responses

### Authentication Flow

1. User registers/logs in via `/api/auth/register` or `/api/auth/login`
2. Backend returns access token
3. Token is stored locally and included in subsequent requests
4. Protected routes use `AuthGuard` to verify authentication
5. User can logout via `/api/auth/logout`

### Backend Setup

Make sure the backend is running before starting the frontend:

```bash
# From project root
docker compose up -d
```

Backend API will be available at `http://localhost:8000/api`

## CI/CD Pipeline

This project uses GitHub Actions for continuous integration.

### Automated Checks

On every push and pull request:

1. **Code Linting** - ESLint with Angular rules and accessibility checks
2. **Build Validation** - Production build verification
3. **Docker Build** - Production Docker image build test

### Running CI Checks Locally

```bash
# Linting
npm run lint

# Production build
npm run build

# Build production Docker image
docker build -f Dockerfile.prod -t frontend:test .
```

See [CI/CD Documentation](../docs/CI-CD.md) for detailed pipeline information.

## Production Deployment

### Building for Production

```bash
npm run build
```

The build artifacts will be stored in the `dist/` directory.

### Production Docker Image

Build the optimized production image:

```bash
docker build -f Dockerfile.prod -t tumi-frontend:latest .
```

The production Dockerfile:
- Uses multi-stage build for optimization
- Serves static files via Nginx
- Includes security headers and gzip compression
- Minimal image size

### Environment Configuration

Update `src/environments/environment.prod.ts` for production API endpoint.

## Contributing

1. Create a feature branch from `develop`
2. Make your changes following Angular style guide
3. Ensure linting passes: `npm run lint`
4. Ensure builds successfully: `npm run build`
5. Submit a pull request to `develop`

### Code Standards

- Follow [Angular Style Guide](https://angular.dev/style-guide)
- Use Angular ESLint rules (enforced by CI)
- Write semantic HTML with accessibility in mind
- Use TypeScript strict mode
- Follow component-based architecture

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## License

This project is proprietary software. All rights reserved.