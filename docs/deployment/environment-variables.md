# Environment Variables Reference

This document describes all environment variables used in the Tumi Configurator backend.
Copy `.env.example` to `.env` and adjust values for your environment.

---

## Application

| Variable | Required | Default | Description |
|---|---|---|---|
| `APP_NAME` | Yes | `Tumi Configurator` | Application name |
| `APP_ENV` | Yes | `local` | `local`, `staging`, `production` |
| `APP_KEY` | Yes | — | Laravel encryption key (generate with `php artisan key:generate`) |
| `APP_DEBUG` | Yes | `true` | Set to `false` in production |
| `APP_URL` | Yes | `http://localhost:8000` | Full base URL of the application |

---

## Database

| Variable | Required | Default | Description |
|---|---|---|---|
| `DB_CONNECTION` | Yes | `pgsql` | Database driver |
| `DB_HOST` | Yes | `postgres` | Database hostname |
| `DB_PORT` | Yes | `5432` | Database port |
| `DB_DATABASE` | Yes | `tumi` | Database name |
| `DB_USERNAME` | Yes | `tumi` | Database user |
| `DB_PASSWORD` | Yes | `tumi_pwd` | Database password |

---

## Redis / Cache / Queue

| Variable | Required | Default | Description |
|---|---|---|---|
| `REDIS_HOST` | Yes | `redis` | Redis hostname |
| `REDIS_PORT` | Yes | `6379` | Redis port |
| `REDIS_PASSWORD` | No | `null` | Redis auth password |
| `QUEUE_CONNECTION` | Yes | `redis` | Queue driver |
| `CACHE_STORE` | Yes | `redis` | Cache driver |

---

## Mail

| Variable | Required | Default | Description |
|---|---|---|---|
| `MAIL_MAILER` | Yes | `log` | Mailer driver (`smtp`, `ses`, `log`) |
| `MAIL_HOST` | No | `127.0.0.1` | SMTP host |
| `MAIL_PORT` | No | `2525` | SMTP port |
| `MAIL_USERNAME` | No | — | SMTP username |
| `MAIL_PASSWORD` | No | — | SMTP password |
| `MAIL_FROM_ADDRESS` | Yes | `hello@example.com` | From address for outgoing emails |

---

## Admin User

> [!WARNING]
> Change `ADMIN_PASSWORD` immediately in staging and production. The default is for local development only.

| Variable | Required | Default | Description |
|---|---|---|---|
| `ADMIN_EMAIL` | No | `admin@tumi.com` | Admin account email |
| `ADMIN_PASSWORD` | No | `admin123` | Admin account password (**change in prod!**) |
| `ADMIN_FIRST_NAME` | No | `Admin` | Admin first name |
| `ADMIN_LAST_NAME` | No | `User` | Admin last name |
| `ADMIN_PHONE` | No | — | Admin phone (optional) |
| `ADMIN_ADDRESS` | No | — | Admin address (optional) |

---

## Seed Organisations

Used by `OrganisationSeeder` to create demo installer and provider organisations.

> [!CAUTION]
> These passwords are for development/staging only. Never use defaults in production.

### Installer

| Variable | Required | Default | Description |
|---|---|---|---|
| `INSTALLER_ORG_EMAIL` | No | `installer-org@tumi.com` | Installer org email |
| `INSTALLER_ORG_NAME` | No | `Demo Installer Co.` | Installer org name |
| `INSTALLER_OWNER_EMAIL` | No | `installer@tumi.com` | Owner user email |
| `INSTALLER_OWNER_PASSWORD` | No | `installer123` | Owner user password |

### Provider

| Variable | Required | Default | Description |
|---|---|---|---|
| `PROVIDER_ORG_EMAIL` | No | `provider-org@tumi.com` | Provider org email |
| `PROVIDER_ORG_NAME` | No | `Demo Provider Ltd.` | Provider org name |
| `PROVIDER_OWNER_EMAIL` | No | `provider@tumi.com` | Owner user email |
| `PROVIDER_OWNER_PASSWORD` | No | `provider123` | Owner user password |

---

## Demo & Seeding

| Variable | Required | Default | Description |
|---|---|---|---|
| `SEED_ON_STARTUP` | No | `false` | If `true`, seeds demo data on container startup |
| `DEMO_USER_EMAIL` | No | `demo@tumi.com` | Demo user email |
| `DEMO_USER_PASSWORD` | No | `demo123456` | Demo user password |

---

## AWS / Storage

| Variable | Required | Default | Description |
|---|---|---|---|
| `AWS_ACCESS_KEY_ID` | No | — | AWS key ID (for S3 storage) |
| `AWS_SECRET_ACCESS_KEY` | No | — | AWS secret key |
| `AWS_DEFAULT_REGION` | No | `us-east-1` | AWS region |
| `AWS_BUCKET` | No | — | S3 bucket name |
| `FILESYSTEM_DISK` | Yes | `local` | Storage driver (`local`, `s3`) |

---

## Real-time / Pusher

| Variable | Required | Default | Description |
|---|---|---|---|
| `PUSHER_APP_ID` | No | `local` | Pusher app ID |
| `PUSHER_APP_KEY` | No | `local` | Pusher app key |
| `PUSHER_APP_SECRET` | No | `local` | Pusher secret |
| `PUSHER_HOST` | No | `soketi` | WebSocket host |
| `PUSHER_PORT` | No | `6001` | WebSocket port |

---

## Sanctum

| Variable | Required | Default | Description |
|---|---|---|---|
| `SANCTUM_STATEFUL_DOMAINS` | Yes | `localhost:4200` | Comma-separated list of SPA domains |

---

## Related Documentation

- [Admin Setup Guide](./admin-setup.md)
- [Audit Log Operations](../operations/audit-logs.md)
