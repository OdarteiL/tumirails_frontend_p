<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreApplianceRequest;
use App\Http\Requests\Admin\UpdateApplianceRequest;
use App\Http\Resources\ApplianceResource;
use App\Models\Appliance;
use Illuminate\Http\JsonResponse;

class ApplianceController extends Controller
{
    /**
     * Store a newly created public catalog appliance.
     */
    public function store(StoreApplianceRequest $request): JsonResponse
    {
        $admin = $request->user();

        $appliance = Appliance::create([
            'owner_id' => $admin->id,
            'owner_type' => get_class($admin),
            'name' => $request->name,
            'category_id' => $request->category_id,
            'default_wattage' => $request->default_wattage,
            'default_usage_hours' => $request->default_usage_hours,
            'metadata' => $request->metadata,
            'is_public' => $request->is_public ?? true, // Default to public for admin
            'is_active' => true,
        ]);

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
        $appliance->update($request->only([
            'name',
            'category_id',
            'default_wattage',
            'default_usage_hours',
            'metadata',
            'is_public', // Admins can change visibility
        ]));

        $appliance->load('category');

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
        // Soft delete by setting is_active to false
        $appliance->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Appliance deleted successfully',
        ]);
    }
}
