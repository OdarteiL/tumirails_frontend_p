<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplianceRequest;
use App\Http\Requests\UpdateApplianceRequest;
use App\Http\Resources\ApplianceResource;
use App\Models\Appliance;
use App\Services\ApplianceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplianceController extends Controller
{
    public function __construct(
        private ApplianceService $applianceService
    ) {}

    /**
     * Display a listing of appliances (public + user's private).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $appliances = $this->applianceService->getVisibleAppliances(
            userId: $user->id,
            userType: get_class($user),
            categoryId: $request->integer('category_id') ?: null,
            search: $request->string('search')->toString() ?: null,
            perPage: 15
        );

        return response()->json([
            'success' => true,
            'data' => ApplianceResource::collection($appliances->items()),
            'meta' => [
                'current_page' => $appliances->currentPage(),
                'last_page' => $appliances->lastPage(),
                'per_page' => $appliances->perPage(),
                'total' => $appliances->total(),
            ],
        ]);
    }

    /**
     * Store a newly created appliance (private custom appliance).
     */
    public function store(StoreApplianceRequest $request): JsonResponse
    {
        $user = $request->user();

        $appliance = $this->applianceService->createAppliance(
            ownerId: $user->id,
            ownerType: get_class($user),
            data: $request->validated(),
            isPublic: false
        );

        $appliance->load('category');

        return response()->json([
            'success' => true,
            'data' => new ApplianceResource($appliance),
            'message' => 'Appliance created successfully',
        ], 201);
    }

    /**
     * Display the specified appliance.
     */
    public function show(Request $request, Appliance $appliance): JsonResponse
    {
        $user = $request->user();

        if (! $this->applianceService->canView($appliance, $user->id, get_class($user))) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to view this appliance',
            ], 403);
        }

        $appliance->load('category');

        return response()->json([
            'success' => true,
            'data' => new ApplianceResource($appliance),
        ]);
    }

    /**
     * Update the specified appliance.
     */
    public function update(UpdateApplianceRequest $request, Appliance $appliance): JsonResponse
    {
        $appliance = $this->applianceService->updateAppliance(
            $appliance,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => new ApplianceResource($appliance),
            'message' => 'Appliance updated successfully',
        ]);
    }

    /**
     * Soft delete the specified appliance (set is_active to false).
     */
    public function destroy(Request $request, Appliance $appliance): JsonResponse
    {
        $user = $request->user();

        if (! $this->applianceService->isOwner($appliance, $user->id, get_class($user))) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to delete this appliance',
            ], 403);
        }

        $this->applianceService->deleteAppliance($appliance);

        return response()->json([
            'success' => true,
            'message' => 'Appliance deleted successfully',
        ]);
    }
}
