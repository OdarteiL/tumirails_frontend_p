<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrganisationStatusRequest;
use App\Http\Resources\AuditLogResource;
use App\Http\Resources\OrganisationResource;
use App\Models\Organisation;
use App\Services\AuditLogService;
use App\Services\OrganisationAdministrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganisationController extends Controller
{
    public function __construct(
        protected OrganisationAdministrationService $organisationAdministrationService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Get a paginated list of organisations with filtering and search (Admin only).
     */
    public function index(\Illuminate\Http\Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $organisations = Organisation::query()
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when(
                $request->search,
                fn ($q, $search) => $q->where(function ($query) use ($search) {
                    $query->where('name', $this->likeOperator(), "%{$search}%")
                        ->orWhere('email', $this->likeOperator(), "%{$search}%");
                })
            )
            ->with(['installerDetail', 'providerDetail'])
            ->withCount(['sites', 'estimations', 'members'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return OrganisationResource::collection($organisations)->additional([
            'success' => true,
            'message' => 'Organisations retrieved successfully',
        ]);
    }

    /**
     * Update organisation status (Admin only).
     */
    public function updateStatus(UpdateOrganisationStatusRequest $request, Organisation $organisation): JsonResponse
    {
        try {
            $updatedOrg = $this->organisationAdministrationService->updateOrganisationStatus(
                organisation: $organisation,
                newStatus: $request->validated('status'),
                adminUser: auth()->user(),
                reason: $request->validated('reason')
            );

            return (new OrganisationResource($updatedOrg))
                ->additional([
                    'success' => true,
                    'message' => 'Organisation status updated successfully',
                ])
                ->response()
                ->setStatusCode(200);

        } catch (\App\Exceptions\User\InvalidStatusTransitionException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        } catch (\App\Exceptions\UnauthorizedException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Get paginated audit logs for a specific organisation (Admin only).
     */
    public function auditLogs(Request $request, Organisation $organisation): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $logs = $this->auditLogService->getLogsForAuditable($organisation, $request->only(['action', 'date_from', 'date_to', 'per_page']));

        return AuditLogResource::collection($logs)->additional([
            'success' => true,
            'message' => 'Organisation audit logs retrieved successfully',
        ]);
    }
}
