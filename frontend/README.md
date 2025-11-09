# Tumi Configurator Frontend

Angular frontend application for the Tumi Solar Configurator platform.

## Features

- User authentication (register, login, logout)
- Reactive forms with validation
- Angular signals for state management
- Tailwind CSS for styling
- Route guards for protected pages

## Development

1. Install dependencies:
```bash
npm install
```

2. Start the development server:
```bash
ng serve
```

3. Navigate to `http://localhost:4200`

## Authentication Flow

- `/login` - User login
- `/register` - User registration  
- `/dashboard` - Protected dashboard (requires authentication)

## Backend Integration

The frontend connects to the Laravel backend API at `http://localhost:8000/api`.

Make sure the backend is running before starting the frontend application.