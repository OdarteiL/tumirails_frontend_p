<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrganisationRequest;
use App\Http\Requests\InviteOrganisationMemberRequest;
use App\Http\Requests\UpdateOrganisationMemberRequest;
use App\Http\Requests\UpdateOrganisationRequest;
use App\Http\Resources\OrganisationInvitationResource;
use App\Http\Resources\OrganisationMemberResource;
use App\Http\Resources\OrganisationResource;
use App\Models\Organisation;
use App\Models\OrganisationMember;
use App\Services\OrganisationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganisationController extends Controller
{
    public function __construct(private OrganisationService $organisationService) {}

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
     * Get members of an organisation.
     */
    public function members(Request $request, Organisation $organisation): JsonResponse
    {
        // Check if user belongs to organisation
        if (! $request->user()->belongsToOrganisation($organisation->id)) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have access to this organisation',
            ], 403);
        }

        $members = $organisation->members()->with('user')->get();

        return response()->json([
            'success' => true,
            'message' => 'Organisation members retrieved successfully',
            'data' => OrganisationMemberResource::collection($members),
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
}
