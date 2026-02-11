# Missing Frontend API Integrations

**Generated:** 2026-02-11  
**Status:** Pending Implementation

This document tracks backend API endpoints that are not yet integrated in the frontend services.

## Backend API Routes Reference
**File:** `backend/routes/api.php`

---

## 1. Appliances Service

### Status: ❌ MISSING

### Backend Controller
**Path:** `backend/app/Http/Controllers/Api/ApplianceController.php`

### Endpoints

| Method | Endpoint | Controller Method | Description |
|--------|----------|-------------------|-------------|
| GET | `/appliances` | `index` | List appliances with filters (search, category, pagination) |
| POST | `/appliances` | `store` | Create new appliance |
| GET | `/appliances/{id}` | `show` | Get appliance details |
| PUT | `/appliances/{id}` | `update` | Update appliance |
| DELETE | `/appliances/{id}` | `destroy` | Delete appliance |

### Admin Endpoints
**Controller:** `backend/app/Http/Controllers/Api/Admin/ApplianceController.php`

| Method | Endpoint | Controller Method | Description |
|--------|----------|-------------------|-------------|
| POST | `/admin/appliances` | `store` | Create public appliance (admin only) |
| PUT | `/admin/appliances/{id}` | `update` | Update any appliance (admin only) |
| DELETE | `/admin/appliances/{id}` | `destroy` | Delete any appliance (admin only) |

### Request/Response Models
- **Request:** `backend/app/Http/Requests/StoreApplianceRequest.php`
- **Request:** `backend/app/Http/Requests/UpdateApplianceRequest.php`
- **Resource:** `backend/app/Http/Resources/ApplianceResource.php`

---

## 2. Sites Service

### Status: ❌ MISSING

### Backend Controller
**Path:** `backend/app/Http/Controllers/Api/SiteController.php`

### Endpoints

| Method | Endpoint | Controller Method | Description |
|--------|----------|-------------------|-------------|
| GET | `/sites` | `index` | List user sites |
| POST | `/sites` | `store` | Create new site |
| GET | `/sites/{id}` | `show` | Get site details |
| POST | `/sites/{site}/appliances` | `addAppliance` | Add appliance to site |
| GET | `/sites/{site}/appliances` | `appliances` | List site appliances |
| DELETE | `/sites/{site}/appliances/{siteAppliance}` | `removeAppliance` | Remove appliance from site |

### Request/Response Models
- **Request:** `backend/app/Http/Requests/CreateSiteRequest.php`
- **Request:** `backend/app/Http/Requests/AddApplianceToSiteRequest.php`
- **Resource:** `backend/app/Http/Resources/SiteResource.php`
- **Resource:** `backend/app/Http/Resources/SiteApplianceResource.php`

---

## 3. Estimations Service (Authenticated)

### Status: ⚠️ PARTIAL (only guest endpoints implemented)

### Backend Controller
**Path:** `backend/app/Http/Controllers/Api/EstimationController.php`

### Missing Endpoints

| Method | Endpoint | Controller Method | Description |
|--------|----------|-------------------|-------------|
| GET | `/estimations` | `index` | List user estimations |
| POST | `/estimations` | `store` | Create estimation for authenticated user |
| GET | `/estimations/{id}` | `show` | Get estimation details |
| PUT | `/estimations/{id}` | `update` | Update estimation |
| DELETE | `/estimations/{id}` | `destroy` | Delete estimation |

### Currently Implemented
- ✅ `POST /estimations/guest` - Guest estimation
- ✅ `GET /estimations/guest/{code}` - Fetch guest estimation

### Request/Response Models
- **Request:** `backend/app/Http/Requests/CreateEstimationRequest.php`
- **Request:** `backend/app/Http/Requests/UpdateEstimationRequest.php`
- **Resource:** `backend/app/Http/Resources/EstimationResource.php`
- **Resource:** `backend/app/Http/Resources/GuestEstimationResource.php`

---

## 4. Recommendations Service

### Status: ❌ MISSING

### Backend Controller
**Path:** `backend/app/Http/Controllers/Api/RecommendationController.php`

### Endpoints

| Method | Endpoint | Controller Method | Description |
|--------|----------|-------------------|-------------|
| GET | `/estimations/{estimation}/recommendations` | `index` | Generate hardware recommendations |
| POST | `/estimations/{estimation}/recommendations` | `store` | Save/persist recommendation bundle |
| GET | `/estimations/{estimation}/recommendation-bundles` | `bundles` | Get saved recommendation bundles |

### Request/Response Models
- **Request:** `backend/app/Http/Requests/StoreRecommendationBundleRequest.php`
- **Resource:** `backend/app/Http/Resources/RecommendationResource.php`
- **Resource:** `backend/app/Http/Resources/RecommendationBundleResource.php`
- **Resource:** `backend/app/Http/Resources/RecommendationBundleComponentResource.php`

---

## 5. Organisations Service

### Status: ❌ MISSING

### Backend Controller
**Path:** `backend/app/Http/Controllers/Api/OrganisationController.php`

### Endpoints

| Method | Endpoint | Controller Method | Description |
|--------|----------|-------------------|-------------|
| GET | `/organisations` | `index` | List user organisations |
| POST | `/organisations` | `store` | Create organisation |
| GET | `/organisations/{id}` | `show` | Get organisation details |
| PUT | `/organisations/{id}` | `update` | Update organisation |
| DELETE | `/organisations/{id}` | `destroy` | Delete organisation |
| GET | `/organisations/{id}/members` | `members` | List organisation members |
| POST | `/organisations/{id}/invite` | `inviteMember` | Invite member to organisation |
| PATCH | `/organisations/{id}/members/{member}` | `updateMember` | Update member role |
| DELETE | `/organisations/{id}/members/{member}` | `removeMember` | Remove member from organisation |
| POST | `/invitations/accept` | `acceptInvitation` | Accept organisation invitation |
| POST | `/invitations/reject` | `rejectInvitation` | Reject organisation invitation |

### Request/Response Models
- **Request:** `backend/app/Http/Requests/CreateOrganisationRequest.php`
- **Request:** `backend/app/Http/Requests/UpdateOrganisationRequest.php`
- **Request:** `backend/app/Http/Requests/InviteOrganisationMemberRequest.php`
- **Request:** `backend/app/Http/Requests/UpdateOrganisationMemberRequest.php`
- **Resource:** `backend/app/Http/Resources/OrganisationResource.php`
- **Resource:** `backend/app/Http/Resources/OrganisationMemberResource.php`
- **Resource:** `backend/app/Http/Resources/OrganisationInvitationResource.php`

---

## 6. Organisation Sites Service

### Status: ❌ MISSING

### Backend Controller
**Path:** `backend/app/Http/Controllers/Api/SiteController.php`

### Endpoints

| Method | Endpoint | Controller Method | Description |
|--------|----------|-------------------|-------------|
| GET | `/organisations/{organisation}/sites` | `organisationIndex` | List organisation sites |
| POST | `/organisations/{organisation}/sites` | `organisationStore` | Create organisation site |
| GET | `/organisations/{organisation}/sites/{siteId}` | `organisationShow` | Get organisation site details |
| POST | `/organisations/{organisation}/sites/{siteId}/appliances` | `addApplianceToOrganisationSite` | Add appliance to org site |
| GET | `/organisations/{organisation}/sites/{siteId}/appliances` | `organisationAppliances` | List org site appliances |
| DELETE | `/organisations/{organisation}/sites/{siteId}/appliances/{siteApplianceId}` | `organisationRemoveAppliance` | Remove appliance from org site |

### Request/Response Models
- Same as Sites Service (shared controller)

---

## 7. Organisation Estimations Service

### Status: ❌ MISSING

### Backend Controller
**Path:** `backend/app/Http/Controllers/Api/EstimationController.php`

### Endpoints

| Method | Endpoint | Controller Method | Description |
|--------|----------|-------------------|-------------|
| GET | `/organisations/{organisation}/estimations` | `organisationIndex` | List organisation estimations |

### Request/Response Models
- **Resource:** `backend/app/Http/Resources/EstimationResource.php`

---

## 8. Contact Service

### Status: ❌ MISSING

### Backend Controller
**Path:** `backend/app/Http/Controllers/Api/ContactController.php`

### Endpoints

| Method | Endpoint | Controller Method | Description | Auth Required |
|--------|----------|-------------------|-------------|---------------|
| POST | `/contact` | `store` | Submit contact form | No (public) |
| GET | `/admin/contacts` | `index` | List all contacts | Yes (admin) |
| GET | `/admin/contacts/{id}` | `show` | Get contact details | Yes (admin) |

### Request/Response Models
- **Request:** `backend/app/Http/Requests/ContactRequest.php`
- **Resource:** `backend/app/Http/Resources/ContactResource.php`

---

## 9. Reverse Estimation

### Status: ❌ MISSING

### Backend Controller
**Path:** `backend/app/Http/Controllers/Api/ReverseEstimationController.php`

### Endpoints

| Method | Endpoint | Controller Method | Description | Auth Required |
|--------|----------|-------------------|-------------|---------------|
| POST | `/estimations/reverse` | `__invoke` | Calculate energy from cost (reverse estimation) | No (public) |

### Request/Response Models
- **Request:** `backend/app/Http/Requests/ReverseEstimationRequest.php`

---

## Implementation Priority

### High Priority (MVP Core Features)
1. **Sites Service** - Required for site management
2. **Appliances Service** - Required for appliance management
3. **Estimations Service (Authenticated)** - Required for logged-in users
4. **Recommendations Service** - Required for hardware recommendations

### Medium Priority (Multi-stakeholder Features)
5. **Organisations Service** - Required for installers/providers
6. **Organisation Sites Service** - Required for org site management
7. **Organisation Estimations Service** - Required for org estimation access

### Low Priority (Additional Features)
8. **Contact Service** - Nice to have for admin
9. **Reverse Estimation** - Additional estimation feature

---

## Frontend Service Structure Recommendation

Create the following service files in `frontend/src/app/services/`:

```
frontend/src/app/services/
├── api.service.ts                    ✅ EXISTS
├── auth.service.ts                   ✅ EXISTS
├── estimations.service.ts            ⚠️ PARTIAL
├── appliances.service.ts             ❌ CREATE
├── sites.service.ts                  ❌ CREATE
├── recommendations.service.ts        ❌ CREATE
├── organisations.service.ts          ❌ CREATE
├── organisation-sites.service.ts     ❌ CREATE
├── organisation-estimations.service.ts ❌ CREATE
├── contact.service.ts                ❌ CREATE
└── reverse-estimation.service.ts     ❌ CREATE
```

---

## Notes

- All authenticated endpoints require `auth:sanctum` middleware
- Admin endpoints require `isAdmin` middleware
- Public endpoints: `/contact`, `/estimations/guest`, `/estimations/reverse`
- Rate limiting applied to public endpoints: `throttle:10,1`
- Backend uses Laravel API Resources for response formatting
- Backend uses Form Request classes for validation

---

## Related Documentation

- **API Routes:** `backend/routes/api.php`
- **OpenAPI Spec:** `docs/api/openapi.yaml`
- **API Endpoints Guide:** `docs/api/api-endpoints.md`
- **Database Schema:** `docs/architecture/database-schema.md`
