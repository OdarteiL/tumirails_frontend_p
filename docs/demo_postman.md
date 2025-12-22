# Demo walkthrough (curl) and Postman guide

This document shows how to run the demo locally (curl examples) and how to reproduce the same steps in Postman. It also explains how the estimation calculation is implemented so you can answer technical questions.

> Assumes backend is running at `http://localhost:8000` and demo data has been seeded with:
>
> ```bash
> docker compose exec backend php artisan app:seed-demo
> ```
---

## 1) Seed demo data (if not already seeded)

```bash
# Option A: Idempotent seed (recommended)
docker compose exec backend php artisan app:seed-demo

# Option B: Reset and seed (fresh demo)
docker compose exec backend php artisan migrate:fresh --force
docker compose exec backend php artisan app:seed-demo
```

The command prints demo credentials. Defaults are:
- Email: `demo@tumi.com`
- Password: `demo123456`

---

## 2) Login (get bearer token)

Curl (Linux/macOS):

```bash
# login and extract token into SHELL variable
TOKEN=$(curl -s -X POST "http://localhost:8000/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"demo@tumi.com","password":"demo123456"}' \
  | tr -d '\n' | grep -oP '"access_token":"\K[^\"]+')

# quick check
echo "$TOKEN"
```

Windows (PowerShell):

```powershell
$response = Invoke-RestMethod -Method POST -Uri http://localhost:8000/api/auth/login -Body (@{email='demo@tumi.com'; password='demo123456'} | ConvertTo-Json) -ContentType 'application/json'
$token = $response.data.access_token
Write-Output $token
```

Successful login returns JSON with `access_token` you must pass as `Authorization: Bearer <token>` for protected endpoints.

---

## 3) List sites for the demo user

```bash
curl -s -H "Authorization: Bearer $TOKEN" \
  http://localhost:8000/api/sites | jq
```

Example response (abbreviated):

```json
{
  "success": true,
  "message": "Sites retrieved successfully",
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "name": "Demo Residential Home",
      "address": "123 Independence Avenue, Accra, Ghana",
      "latitude": 5.6037,
      "longitude": -0.1870
    }
  ]
}
```

Take note of the `id` of the demo site (e.g. `1`).

---

## 4) Add or inspect appliances on the site

The demo seeder attaches several appliances automatically. To add an appliance to the site (for testing), use the `POST /api/sites/{site}/appliances` endpoint.

Example: add 1 refrigerator (appliance id 1) for 24 hours/day

```bash
curl -s -X POST "http://localhost:8000/api/sites/1/appliances" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"appliance_id":1,"quantity":1,"daily_usage_hours":24}' | jq
```

The `SiteApplianceResource` return structure will include the stored appliance entry.

> Note: `appliance_id` should reference an existing entry from `appliances` table. Use `GET /api/appliances` to inspect the catalog.

---

## 5) Create a new estimation for the site

Create an estimation (server calculates and stores it):

```bash
curl -s -X POST "http://localhost:8000/api/estimations" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"site_id": 1}' | jq
```

Response contains the `EstimationResource` with fields:
- `total_watts`
- `daily_kwh`
- `monthly_kwh`
- `adjusted_monthly_kwh`
- `estimated_monthly_cost`
- `appliances_breakdown` (per-appliance daily kWh and monthly cost shares)

Example (abbreviated):

```json
{
  "success": true,
  "message": "Estimation created successfully",
  "data": {
    "id": 1,
    "version": 1,
    "total_watts": 1200,
    "daily_kwh": 10.8,
    "monthly_kwh": 324,
    "adjusted_monthly_kwh": 324,
    "estimated_monthly_cost": 395.94,
    "appliances_breakdown": [ ... ]
  }
}
```

---

## 6) Retrieve an existing estimation

List your estimations:

```bash
curl -s -H "Authorization: Bearer $TOKEN" http://localhost:8000/api/estimations | jq
```

OR fetch a single estimation by id:

```bash
curl -s -H "Authorization: Bearer $TOKEN" http://localhost:8000/api/estimations/1 | jq
```

---

## 7) Recalculate (update) an estimation

To force a recalculation (and either update existing estimation or create a new version depending on snapshot change):

```bash
curl -s -X PUT "http://localhost:8000/api/estimations/1" \
  -H "Authorization: Bearer $TOKEN" | jq
```

---

## Postman setup (quick)

1. Create an Environment variable set named `Local` with base URL `http://localhost:8000`.
2. Create a request `Login` (POST `{{baseUrl}}/api/auth/login`) with body JSON:

```json
{"email":"demo@tumi.com","password":"demo123456"}
```

3. After running `Login`, in the `Tests` tab paste this script to store token in environment variable `demo_token`:

```javascript
const json = pm.response.json();
if(json && json.data && json.data.access_token) {
  pm.environment.set('demo_token', json.data.access_token);
}
```

4. For subsequent requests, set Authorization header to `Bearer {{demo_token}}` (or use Authorization type Bearer Token and paste `{{demo_token}}` into token field).

5. Create requests for `GET /api/sites`, `POST /api/estimations`, etc., using the environment base URL.

---

## How estimation is implemented (technical explanation)

Estimation calculation is implemented in two main pieces:

1. `CalculateEstimationAction` — performs the energy calculations and cost estimation.
2. `StoreEstimationAction` — persists the results with versioning and snapshot logic.

### Step-by-step calculation (CalculateEstimationAction)

- Load all `SiteAppliance` entries for the site (each references an `Appliance` with `default_wattage` and `default_usage_hours`).

- For each site appliance:
  - Determine the `power_factor` from its `Category` (fallback 0.90 if not set).
  - Compute appliance watts: `watts = appliance.default_wattage * quantity`.
  - Compute daily kWh for the appliance:

    daily_kwh_appliance = (appliance.default_wattage * quantity * daily_usage_hours * power_factor) / 1000

  - Add to running totals: `totalWatts` and `dailyKwh`.

- Monthly kWh is `monthly_kwh = daily_kwh * 30`.

- Apply seasonal multiplier (if present): `adjusted_monthly_kwh = monthly_kwh * seasonal_multiplier`.

- Apply optional location multiplier (if present): `final_monthly_kwh = adjusted_monthly_kwh * location_multiplier`.

- Compute cost using the tariff structure:
  - If the tariff structure type is `flat`, cost = `kwh * rate_per_kwh` (use first tier rate).
  - If `tiered`, cost is computed per tier: the algorithm iterates tiers (ordered) and allocates kWh to each tier's capacity, multiplying by tier rate and summing.

- Distribute the estimated monthly cost back to each appliance proportionally by its share of the final monthly kWh (so each appliance has `monthly_cost`).

- Return a detailed breakdown including `power_factor_applied`, `seasonal_multiplier`, `location_multiplier`, and `appliances_breakdown`.

### Persistence & versioning (StoreEstimationAction)

- `StoreEstimationAction` checks if a previous estimation exists for the same site+owner.
- It compares the `appliances_snapshot` (id, quantity, hours) to decide whether appliances changed.
  - If appliances changed (quantity or hours differ or appliance list length differs), it creates a new `Estimation` with `version = previous.version + 1` and links `previous_estimation_id`.
  - If appliances did not change, it updates the existing estimation in place (keeps the same version).

- This allows traceability (you can keep previous versions when equipment changes) while avoiding noisy version increments on recalculations where the appliance snapshot is unchanged.

---

## Troubleshooting

- If you get authentication errors, ensure `Authorization: Bearer <token>` header is included and token is not expired.
- If the demo seeder fails, run `php artisan migrate:fresh --force` then `php artisan app:seed-demo`.
- If `appliance_id` references are wrong, run `GET /api/appliances` to discover catalog IDs.

---

## Appendix: Useful endpoints

- POST `/api/auth/login` — login
- GET `/api/sites` — list user sites
- POST `/api/sites/{site}/appliances` — add appliance to site
- POST `/api/estimations` — create estimation (body: `{"site_id": <id>}`)
- GET `/api/estimations` — list estimations
- GET `/api/estimations/{id}` — get estimation details
- PUT `/api/estimations/{id}` — recalculate/update estimation

---

If you want, I can:
- Export a Postman collection (JSON) with these requests set up and environment variables pre-configured.
- Run the curl steps here and paste the real responses (I already seeded demo and verified tests; I can fetch live responses and attach them to the doc).

Which would you prefer next?