<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplianceRequest;
use App\Http\Requests\UpdateApplianceRequest;
use App\Http\Resources\ApplianceResource;
use App\Models\Appliance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ApplianceController extends Controller
{
    /**
     * Display a listing of appliances (public + user's private).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = Appliance::query()
            ->with('category')
            ->visibleTo($user->id, get_class($user));

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'ILIKE', '%' . $request->search . '%');
        }

        $appliances = $query->paginate(15);

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

        $appliance = Appliance::create([
            'owner_id' => $user->id,
            'owner_type' => get_class($user),
            'name' => $request->name,
            'category_id' => $request->category_id,
            'default_wattage' => $request->default_wattage,
            'default_usage_hours' => $request->default_usage_hours,
            'metadata' => $request->metadata,
            'is_public' => false, // User appliances are always private
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
     * Display the specified appliance.
     */
    public function show(Request $request, Appliance $appliance): JsonResponse
    {
        $user = $request->user();

        // Check if appliance is public OR owned by user
        if (!$appliance->is_public) {
            if ($appliance->owner_id !== $user->id || $appliance->owner_type !== get_class($user)) {
                return response()->json([
                    'success' => false,
                    'error' => 'You do not have permission to view this appliance',
                ], 403);
            }
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
        // Authorization is handled in UpdateApplianceRequest
        
        $appliance->update($request->only([
            'name',
            'category_id',
            'default_wattage',
            'default_usage_hours',
            'metadata',
        ]));

        $appliance->load('category');

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

        // Check ownership
        if ($appliance->owner_id !== $user->id || $appliance->owner_type !== get_class($user)) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to delete this appliance',
            ], 403);
        }

        // Soft delete by setting is_active to false
        $appliance->update(['is_active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Appliance deleted successfully',
        ]);
    }
}
