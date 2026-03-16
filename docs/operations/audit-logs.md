# Audit Log Retention & Operations Guide

## Overview

Audit logs record every significant action taken by users and administrators — status changes, membership updates, and administrative decisions. They serve as the system of record for compliance, debugging, and security investigations.

---

## What is Logged

| Action | Subject | Who logs it | Notes |
|---|---|---|---|
| `status_changed` | `User` | Admin | User suspended or reactivated |
| `status_changed` | `Organisation` | Admin | Org suspended or reactivated |
| `status_changed` | `OrganisationMember` | Admin or Org Owner | Member suspended or reactivated |
| `created` | Any model | Creator | Resource creation (where configured) |
| `updated` | Any model | Editor | Field-level changes |
| `assigned` / `unassigned` | Any model | Editor | Relationship changes |

### Data captured per log entry

- `user_id` — who performed the action
- `auditable_type` / `auditable_id` — polymorphic: the model that changed
- `action` — action type (e.g. `status_changed`)
- `old_values` — JSON snapshot of values before the change
- `new_values` — JSON snapshot of values after the change
- `reason` — optional human-readable justification
- `ip_address` — captured from the HTTP request
- `user_agent` — captured from the HTTP request
- `created_at` — timestamp of the action

---

## API Endpoints

All audit log endpoints require Admin privileges.

| Endpoint | Description |
|---|---|
| `GET /api/admin/users/{user}/audit-logs` | Logs for a specific user |
| `GET /api/admin/organisations/{org}/audit-logs` | Logs for a specific organisation |
| `GET /api/organisations/{org}/members/{member}/audit-logs` | Logs for a specific org member |

### Query parameters (all endpoints)

| Parameter | Type | Description |
|---|---|---|
| `action` | string | Filter by action type (e.g. `status_changed`) |
| `date_from` | date | Filter logs after this date |
| `date_to` | date | Filter logs before this date |
| `per_page` | integer | Results per page (default: 15) |

### Example: fetch user audit logs

```bash
curl -H "Authorization: Bearer {token}" \
  "https://api.tumi-energy.com/v1/admin/users/42/audit-logs?action=status_changed&per_page=25"
```

---

## Querying via Database

### Recent status changes (last 30 days)

```sql
SELECT al.*, u.email AS performed_by
FROM audit_logs al
JOIN users u ON u.id = al.user_id
WHERE al.action = 'status_changed'
  AND al.created_at >= NOW() - INTERVAL '30 days'
ORDER BY al.created_at DESC;
```

### All audit logs for a specific user

```sql
SELECT * FROM audit_logs
WHERE auditable_type = 'App\Models\User'
  AND auditable_id = 42
ORDER BY created_at DESC;
```

### All audit logs for an organisation and its members

```sql
SELECT * FROM audit_logs
WHERE (auditable_type = 'App\Models\Organisation' AND auditable_id = 5)
   OR (auditable_type = 'App\Models\OrganisationMember'
       AND auditable_id IN (
           SELECT id FROM organisation_members WHERE organisation_id = 5
       ))
ORDER BY created_at DESC;
```

### Admin actions in the last 7 days

```sql
SELECT al.*, u.email AS admin_email
FROM audit_logs al
JOIN users u ON u.id = al.user_id
WHERE u.role = 'admin'
  AND al.created_at >= NOW() - INTERVAL '7 days'
ORDER BY al.created_at DESC;
```

---

## Retention Policy

| Environment | Recommended Retention | Rationale |
|---|---|---|
| Production | 2 years minimum | Regulatory compliance |
| Staging | 90 days | Debugging purposes |
| Local/CI | Session only (DB reset) | No retention needed |

### Compliance considerations

- **GDPR / data protection laws**: Audit logs may contain PII (IP addresses, user agents). Ensure your privacy policy discloses this.
- **Right to erasure**: Deleting a user's PII from `audit_logs` (IP, user agent) may be required upon erasure request. The log entry itself (action/timestamps) may need to be retained for legal audit trails — consult legal counsel.
- **GDPR Article 30**: Maintain a record of processing activities that includes audit logging purposes.

---

## Privacy Considerations

- **IP address and user agent** are only visible to admins in API responses (`AuditLogResource` hides them from non-admins)
- Logs link to users who may later be deleted — use soft deletes or anonymize `user_id` references for archived logs
- **Bulk export / archival**: JSON-export audit logs before purging; store in encrypted cold storage (e.g., S3 with AES-256)

---

## Backup and Archival

### Recommended schedule

```
Daily   → Incremental backup of audit_logs (last 24h rows)
Weekly  → Full backup snapshot
Monthly → Archive logs older than 12 months to cold storage
Yearly  → Purge logs older than retention period (after archival)
```

### Export to JSON (artisan command — future)

```bash
# Planned: export logs older than 2 years to S3
php artisan audit-logs:archive --older-than="2 years" --output=s3://tumi-archive/audit/
```

### Manual export via psql

```bash
# Export to JSON
psql $DATABASE_URL -c "\COPY (SELECT * FROM audit_logs WHERE created_at < NOW() - INTERVAL '2 years') TO '/tmp/audit_archive.csv' CSV HEADER"
```

---

## Monitoring and Alerts

Watch for these patterns as indicators of suspicious activity:

| Pattern | Threshold | Action |
|---|---|---|
| Multiple suspensions by same admin | > 10 in 1 hour | Alert security team |
| Repeated failed login attempts | > 5 in 5 minutes | Rate-limit / alert |
| Admin account suspended | Any occurrence | Immediate alert |
| Bulk organisation suspensions | > 3 in 1 hour | Manual review required |

> [!NOTE]
> Automated alerting is a planned feature. For now, review audit logs weekly via the admin API.

---

## Compliance Checklist

- [ ] Audit logs are enabled for all status change operations
- [ ] `ip_address` and `user_agent` are captured per request
- [ ] `old_values` and `new_values` are stored as JSON
- [ ] Only admins can access audit log API endpoints
- [ ] Retention policy documented and agreed with legal team
- [ ] Backup schedule is configured and tested
- [ ] Archival process defined for logs exceeding retention period
- [ ] Privacy policy updated to disclose audit log data collection
- [ ] GDPR data erasure process documented

---

## Related Documentation

- [Admin Setup Guide](../deployment/admin-setup.md)
- [API Endpoints](../api/api-endpoints.md)
- [Environment Variables](../deployment/environment-variables.md)
