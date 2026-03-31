# Estimator to Dashboard Integration

## Flow Implementation

### Customer Flow
1. **Estimator** (`/estimator`)
   - Customer fills out appliance details
   - Clicks "Calculate Estimation"
   - System generates a unique token (reference code)
   - Token is stored in `localStorage` as `estimation_token`
   - Success modal shows for 1 second
   - Auto-redirects to `/register` after 3 seconds

2. **Registration** (`/register`)
   - Customer creates account with role: 'customer'
   - After successful registration, redirects to `/customer/dashboard`

3. **Customer Dashboard** (`/customer/dashboard`)
   - Loads token from `localStorage`
   - Fetches estimation details using the token
   - Displays:
     - Monthly estimated cost (GH₵)
     - Estimation token
   - Token persists across sessions until cleared

### Other User Roles Flow
- **Vendor/Provider**: Login → `/vendor/dashboard`
- **Installer**: Login → `/installer/dashboard`
- **Admin**: Login → `/admin/dashboard`

## Technical Implementation

### Files Modified

1. **Estimator Component** (`estimator.ts`)
   - Added Router injection
   - Store token in localStorage after generation
   - Auto-redirect to register page after 3 seconds

2. **Customer Dashboard** (`consumer/dashboard/dashboard.ts`)
   - Added EstimationsService injection
   - Added signals: `estimationToken`, `monthlyEstimate`, `estimations`
   - Load token from localStorage on init
   - Fetch estimation details by token
   - Display monthly cost and token in first stat card

3. **Login Component** (`login.component.ts`)
   - Already implements role-based routing:
     - customer/consumer → `/customer/marketplace`
     - provider → `/vendor/dashboard`
     - installer → `/installer/dashboard`
     - admin → `/admin/dashboard`

4. **Register Component** (`register.component.ts`)
   - Updated to redirect customers to `/customer/dashboard`
   - Other roles redirect to generic `/dashboard`

5. **Estimator Success Modal** (`estimator.html`)
   - Updated message to indicate redirect
   - Changed "Reference Code" to "Token"
   - Updated instructions for customer dashboard

## Data Flow

```
Estimator
  ↓ (generates token)
localStorage.setItem('estimation_token', token)
  ↓ (redirect after 3s)
Register Page
  ↓ (create account)
Customer Dashboard
  ↓ (on load)
localStorage.getItem('estimation_token')
  ↓ (if token exists)
EstimationsService.getGuestEstimationByCode(token)
  ↓ (display)
Monthly Cost + Token in Dashboard
```

## API Endpoints Used

- `POST /estimations/guest` - Create guest estimation
- `GET /estimations/guest/:code` - Get estimation by token
- `GET /estimations` - Get user's estimations (authenticated)
- `POST /auth/register` - Register new user
- `POST /auth/login` - Login user

## LocalStorage Keys

- `estimation_token` - Stores the generated token from estimator
- `access_token` - Auth token (existing)
- `user` - User data (existing)

## Future Enhancements

- Link token to user account after registration
- Show all estimation history in dashboard
- Allow customers to generate multiple estimations
- Clear token after linking to account
- Add token validation on dashboard load
