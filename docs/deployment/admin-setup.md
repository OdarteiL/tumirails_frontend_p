# Admin & Organisation Setup Guide

## Overview

This guide covers how admin users and seed organisations are managed across environments. The admin user is the system-level superuser — they can manage all users, organisations, and view all audit logs. Organisation seed data provides initial installers and providers for development and staging.

---

## Default Credentials

> [!CAUTION]
> Default credentials below are for **development only**. Changing them in staging and production is mandatory.

| Account | Default Email | Default Password | Purpose |
|---|---|---|---|
| Admin User | `admin@tumi.com` | `admin123` | System administrator |
| Installer Org Owner | `installer@tumi.com` | `installer123` | Demo installer organisation |
| Provider Org Owner | `provider@tumi.com` | `provider123` | Demo provider organisation |

### Environment recommendations

- **Local / CI**: Use defaults via `.env`
- **Staging**: Set unique credentials via environment variables
- **Production**: Must be changed **before first use**; remove defaults

---

## Creating the Admin User

### Via Seeder (recommended)

```bash
# Runs AdminSeeder only
php artisan db:seed --class=AdminSeeder

# Or run the full suite (AdminSeeder runs first)
php artisan db:seed
```

### Via Tinker (one-off creation or emergency)

```bash
php artisan tinker
```

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::updateOrCreate(
    ['email' => 'admin@tumi.com'],
    [
        'first_name' => 'Admin',
        'last_name'  => 'User',
        'role'       => 'admin',
        'status'     => 'active',
        'password'   => Hash::make('your-secure-password'),
    ]
);
```

### Order importance

The `AdminSeeder` is called **first** in `DatabaseSeeder::run()` because some demo data (e.g., site ownership) may reference the admin user.

---

## Creating Seed Organisations

```bash
# Installer + Provider organisations with owner users
php artisan db:seed --class=OrganisationSeeder

# Or run the full suite (OrganisationSeeder runs second after AdminSeeder)
php artisan db:seed
```

The seeder creates:
- **Demo Installer Co.** (`installer` type) — owned by `installer@tumi.com`
- **Demo Provider Ltd.** (`provider` type) — owned by `provider@tumi.com`

All created idempotently via `updateOrCreate()`.

---

## Changing the Admin Password

```bash
php artisan tinker
```

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$admin = User::where('email', 'admin@tumi.com')->firstOrFail();
$admin->password = Hash::make('new-very-secure-password-min-12-chars');
$admin->save();

echo "Password updated for: {$admin->email}";
```

---

## Creating Additional Admin Users

You may need multiple named admin accounts (never share a single account).

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'first_name' => 'Jane',
    'last_name'  => 'Admin',
    'email'      => 'jane.admin@yourdomain.com',
    'role'       => 'admin',
    'status'     => 'active',
    'password'   => Hash::make('strong-unique-password'),
]);
```

**When to add more admins:**
- Operational team members who need audit log access
- On-call rotation members

**Limit admin accounts** — every admin can suspend users and view all audit logs. Audit admin actions regularly (see `docs/operations/audit-logs.md`).

---

## Organisation Member Roles

OrganisationMembers have three roles:

| Role | Description |
|---|---|
| `owner` | Full org control; can manage members and update status |
| `admin` | Can manage members; cannot transfer ownership |
| `member` | Standard access; cannot manage other members |

---

## Security Best Practices

> [!WARNING]
> Failure to follow these in production is a security risk.

- **Change default passwords immediately** in staging and production
- **Minimum 12 characters** for all admin passwords; use a password manager
- **No shared accounts** — each admin must have their own named account
- **Review audit logs regularly** — `GET /api/admin/users/{id}/audit-logs`
- **Limit admin count** — fewer admins = smaller attack surface
- **2FA** (planned future feature — track in issues)
- **Rotate credentials** if a team member with admin access leaves

---

## Environment Variables

| Variable | Required | Default | Description |
|---|---|---|---|
| `ADMIN_EMAIL` | No | `admin@tumi.com` | Admin user email |
| `ADMIN_PASSWORD` | No | `admin123` | Admin password (**change in prod!**) |
| `ADMIN_PHONE` | No | `null` | Admin phone number |
| `ADMIN_ADDRESS` | No | `null` | Admin address |
| `INSTALLER_ORG_EMAIL` | No | `installer-org@tumi.com` | Installer org email |
| `INSTALLER_OWNER_EMAIL` | No | `installer@tumi.com` | Installer org owner email |
| `INSTALLER_OWNER_PASSWORD` | No | `installer123` | Installer owner password |
| `PROVIDER_ORG_EMAIL` | No | `provider-org@tumi.com` | Provider org email |
| `PROVIDER_OWNER_EMAIL` | No | `provider@tumi.com` | Provider org owner email |
| `PROVIDER_OWNER_PASSWORD` | No | `provider123` | Provider owner password |

Example `.env` block:

```dotenv
# Admin User Configuration
# WARNING: Change ADMIN_PASSWORD before deploying to staging/production!
ADMIN_EMAIL=admin@tumi.com
ADMIN_PASSWORD=admin123
ADMIN_PHONE=
ADMIN_ADDRESS=

# Seed Organisation Configuration
INSTALLER_ORG_EMAIL=installer-org@tumi.com
INSTALLER_ORG_NAME="Demo Installer Co."
INSTALLER_OWNER_EMAIL=installer@tumi.com
INSTALLER_OWNER_PASSWORD=installer123

PROVIDER_ORG_EMAIL=provider-org@tumi.com
PROVIDER_ORG_NAME="Demo Provider Ltd."
PROVIDER_OWNER_EMAIL=provider@tumi.com
PROVIDER_OWNER_PASSWORD=provider123
```

---

## Troubleshooting

### Cannot login as admin

1. Check the user exists: `User::where('email', 'admin@tumi.com')->first()`
2. Confirm `role` is `admin` and `status` is `active`
3. Reset password via Tinker if needed (see above)

### Admin account suspended

Only another admin can reactivate. Via Tinker:

```php
User::where('email', 'admin@tumi.com')->update(['status' => 'active']);
```

Or via the API if a second admin exists:

```
PATCH /api/admin/users/{id}/status
{ "status": "active", "reason": "Reactivating locked account" }
```

### Forgot admin password

Use Tinker to reset (see [Changing the Admin Password](#changing-the-admin-password)).

### Seeder runs but admin not created

Check for DB constraint failures in the output. Ensure the `users` table has `role` and `status` columns (run `php artisan migrate` first).

---

## Related Documentation

- [Audit Log Retention Policy](../operations/audit-logs.md)
- [Environment Variables Reference](./environment-variables.md)
- [API Endpoints — Admin](../api/api-endpoints.md)
