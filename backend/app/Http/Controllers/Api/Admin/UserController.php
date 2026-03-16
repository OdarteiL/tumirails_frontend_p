<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserStatusRequest;
use App\Http\Resources\AuditLogResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\UserAdministrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected UserAdministrationService $userAdministrationService,
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Get a paginated list of users with filtering and search (Admin only).
     */
    public function index(\Illuminate\Http\Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $users = User::query()
            ->when($request->role, fn ($q, $role) => $q->where('role', $role))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when(
                $request->search,
                fn ($q, $search) => $q->where(function ($query) use ($search) {
                    $query->where('first_name', 'ilike', "%{$search}%")
                        ->orWhere('last_name', 'ilike', "%{$search}%")
                        ->orWhere('email', 'ilike', "%{$search}%");
                })
            )
            ->with(['installerDetail', 'providerDetail'])
            ->withCount(['sites', 'estimations'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return UserResource::collection($users)->additional([
            'success' => true,
            'message' => 'Users retrieved successfully',
        ]);
    }

    /**
     * Update user status (Admin only).
     */
    public function updateStatus(UpdateUserStatusRequest $request, User $user): JsonResponse
    {
        try {
            $updatedUser = $this->userAdministrationService->updateUserStatus(
                targetUser: $user,
                newStatus: $request->validated('status'),
                adminUser: auth()->user(),
                reason: $request->validated('reason')
            );

            return (new UserResource($updatedUser))
                ->additional([
                    'success' => true,
                    'message' => 'User status updated successfully',
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
                'error' => 'An unexpected error occurred while updating the user status.',
            ], 500);
        }
    }

    /**
     * Get paginated audit logs for a specific user (Admin only).
     */
    public function auditLogs(Request $request, User $user): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $logs = $this->auditLogService->getLogsForAuditable($user, $request->only(['action', 'date_from', 'date_to', 'per_page']));

        return AuditLogResource::collection($logs)->additional([
            'success' => true,
            'message' => 'User audit logs retrieved successfully',
        ]);
    }
}
