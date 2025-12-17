<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreApplianceRequest;
use App\Http\Requests\Admin\UpdateApplianceRequest;
use App\Http\Resources\ApplianceResource;
use App\Models\Appliance;
use App\Services\ApplianceService;
use Illuminate\Http\JsonResponse;

class ApplianceController extends Controller
{
    public function __construct(
        private ApplianceService $applianceService
    ) {}

    /**
     * Store a newly created public catalog appliance.
     */
    public function store(StoreApplianceRequest $request): JsonResponse
    {
        $admin = $request->user();

        $appliance = $this->applianceService->createAppliance(
            ownerId: $admin->id,
            ownerType: get_class($admin),
            data: $request->validated(),
            isPublic: $request->validated('is_public', true)
        );

        $appliance->load('category');

        return response()->json([
            'success' => true,
            'data' => new ApplianceResource($appliance),
            'message' => 'Appliance created successfully',
        ], 201);
    }

    /**
     * Update the specified appliance (can modify any appliance).
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
     * Soft delete the specified appliance (can delete any appliance).
     */
    public function destroy(Appliance $appliance): JsonResponse
    {
        $this->applianceService->deleteAppliance($appliance);

        return response()->json([
            'success' => true,
            'message' => 'Appliance deleted successfully',
        ]);
    }
}
