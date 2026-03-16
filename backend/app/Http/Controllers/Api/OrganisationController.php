<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrganisationRequest;
use App\Http\Requests\InviteOrganisationMemberRequest;
use App\Http\Requests\UpdateOrganisationMemberRequest;
use App\Http\Requests\UpdateOrganisationRequest;
use App\Http\Resources\AuditLogResource;
use App\Http\Resources\OrganisationInvitationResource;
use App\Http\Resources\OrganisationMemberResource;
use App\Http\Resources\OrganisationResource;
use App\Models\Organisation;
use App\Models\OrganisationMember;
use App\Services\AuditLogService;
use App\Services\OrganisationAdministrationService;
use App\Services\OrganisationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganisationController extends Controller
{
    public function __construct(
        private OrganisationService $organisationService,
        private OrganisationAdministrationService $organisationAdministrationService,
        private AuditLogService $auditLogService
    ) {}

    /**
     * Get all organisations the user belongs to.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $organisations = $user->organisations()
            ->with(['installerDetail', 'providerDetail', 'members'])
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Organisations retrieved successfully',
            'data' => OrganisationResource::collection($organisations),
        ]);
    }

    /**
     * Create a new organisation.
     */
    public function store(CreateOrganisationRequest $request): JsonResponse
    {
        try {
            $organisation = $this->organisationService->createOrganisation(
                $request->validated(),
                $request->user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Organisation created successfully',
                'data' => new OrganisationResource($organisation),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get a specific organisation.
     */
    public function show(Request $request, Organisation $organisation): JsonResponse
    {
        // Check if user belongs to organisation
        if (! $request->user()->belongsToOrganisation($organisation->id)) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have access to this organisation',
            ], 403);
        }

        $organisation->load(['installerDetail', 'providerDetail', 'members.user']);

        return response()->json([
            'success' => true,
            'message' => 'Organisation retrieved successfully',
            'data' => new OrganisationResource($organisation),
        ]);
    }

    /**
     * Update an organisation.
     */
    public function update(UpdateOrganisationRequest $request, Organisation $organisation): JsonResponse
    {
        // Check if user has permission to edit
        if (! $this->organisationService->userHasPermission($request->user(), $organisation, 'edit')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to edit this organisation',
            ], 403);
        }

        try {
            $organisation = $this->organisationService->updateOrganisation(
                $organisation,
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Organisation updated successfully',
                'data' => new OrganisationResource($organisation),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete an organisation.
     */
    public function destroy(Request $request, Organisation $organisation): JsonResponse
    {
        // Check if user has permission to delete
        if (! $this->organisationService->userHasPermission($request->user(), $organisation, 'delete')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to delete this organisation',
            ], 403);
        }

        try {
            $this->organisationService->deleteOrganisation($organisation);

            return response()->json([
                'success' => true,
                'message' => 'Organisation deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get members of an organisation with filtering.
     */
    public function members(Request $request, Organisation $organisation): \Illuminate\Http\Resources\Json\AnonymousResourceCollection|JsonResponse
    {
        // Check if user belongs to organisation
        if (! $request->user()->belongsToOrganisation($organisation->id)) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have access to this organisation',
            ], 403);
        }

        $members = $organisation->members()
            ->when($request->role, fn ($q, $role) => $q->where('role', $role))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->search, function ($q, $search) {
                $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('first_name', 'ilike', "%{$search}%")
                        ->orWhere('last_name', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%");
                });
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return OrganisationMemberResource::collection($members)->additional([
            'success' => true,
            'message' => 'Organisation members retrieved successfully',
        ]);
    }

    /**
     * Invite a member to an organisation.
     */
    public function inviteMember(InviteOrganisationMemberRequest $request, Organisation $organisation): JsonResponse
    {
        // Check if user has permission to manage members
        if (! $this->organisationService->userHasPermission($request->user(), $organisation, 'manage_members')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to invite members',
            ], 403);
        }

        try {
            $invitation = $this->organisationService->inviteMember(
                $organisation,
                $request->validated(),
                $request->user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Invitation sent successfully',
                'data' => new OrganisationInvitationResource($invitation),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Accept an organisation invitation.
     */
    public function acceptInvitation(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
        ]);

        try {
            $member = $this->organisationService->acceptInvitation(
                $request->input('token'),
                $request->user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Invitation accepted successfully',
                'data' => new OrganisationMemberResource($member),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Reject an organisation invitation.
     */
    public function rejectInvitation(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
        ]);

        try {
            $invitation = $this->organisationService->rejectInvitation(
                $request->input('token'),
                $request->user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Invitation rejected successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update a member's role.
     */
    public function updateMember(
        UpdateOrganisationMemberRequest $request,
        Organisation $organisation,
        OrganisationMember $member
    ): JsonResponse {
        // Check if user has permission to manage members
        if (! $this->organisationService->userHasPermission($request->user(), $organisation, 'manage_members')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to update members',
            ], 403);
        }

        // Verify member belongs to this organisation
        if ($member->organisation_id !== $organisation->id) {
            return response()->json([
                'success' => false,
                'error' => 'Member does not belong to this organisation',
            ], 404);
        }

        try {
            $member = $this->organisationService->updateMember($member, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Member updated successfully',
                'data' => new OrganisationMemberResource($member),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove a member from an organisation.
     */
    public function removeMember(Request $request, Organisation $organisation, OrganisationMember $member): JsonResponse
    {
        // Check if user has permission to manage members
        if (! $this->organisationService->userHasPermission($request->user(), $organisation, 'manage_members')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to remove members',
            ], 403);
        }

        // Verify member belongs to this organisation
        if ($member->organisation_id !== $organisation->id) {
            return response()->json([
                'success' => false,
                'error' => 'Member does not belong to this organisation',
            ], 404);
        }

        try {
            $this->organisationService->removeMember($member);

            return response()->json([
                'success' => true,
                'message' => 'Member removed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update an organisation member's status.
     */
    public function updateMemberStatus(
        \App\Http\Requests\Organisation\UpdateOrganisationMemberStatusRequest $request,
        Organisation $organisation,
        OrganisationMember $member
    ): JsonResponse {
        // Verify member belongs to this organisation
        if ($member->organisation_id !== $organisation->id) {
            return response()->json([
                'success' => false,
                'error' => 'Member does not belong to this organisation',
            ], 404);
        }

        try {
            $updatedMember = $this->organisationAdministrationService->updateOrganisationMemberStatus(
                member: $member,
                newStatus: $request->validated('status'),
                performer: $request->user(),
                reason: $request->validated('reason')
            );

            return (new OrganisationMemberResource($updatedMember))
                ->additional([
                    'success' => true,
                    'message' => 'Member status updated successfully',
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
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'An unexpected error occurred while updating the member status.',
            ], 500);
        }
    }

    /**
     * Get paginated audit logs for a specific organisation member (Admin only).
     */
    public function memberAuditLogs(Request $request, Organisation $organisation, OrganisationMember $member): \Illuminate\Http\Resources\Json\AnonymousResourceCollection|JsonResponse
    {
        // Enforce admin-only access
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'error' => 'Only administrators can view member audit logs.',
            ], 403);
        }

        if ($member->organisation_id !== $organisation->id) {
            return response()->json([
                'success' => false,
                'error' => 'Member does not belong to this organisation',
            ], 404);
        }

        $logs = $this->auditLogService->getLogsForAuditable($member, $request->only(['action', 'date_from', 'date_to', 'per_page']));

        return AuditLogResource::collection($logs)->additional([
            'success' => true,
            'message' => 'Member audit logs retrieved successfully',
        ]);
    }
}
