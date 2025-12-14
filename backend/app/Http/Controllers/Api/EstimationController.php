<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateEstimationRequest;
use App\Http\Requests\UpdateEstimationRequest;
use App\Http\Resources\EstimationResource;
use App\Models\Estimation;
use App\Models\Organisation;
use App\Models\Site;
use App\Services\EstimationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EstimationController extends Controller
{
    public function __construct(
        private readonly EstimationService $estimationService
    ) {}

    /**
     * Create a new estimation for a site.
     * Determines owner from the site's ownership.
     */
    public function store(CreateEstimationRequest $request): JsonResponse
    {
        try {
            // Get the site
            $site = Site::findOrFail($request->validated()['site_id']);

            // Get owner from site
            $owner = $site->owner;

            // Create estimation
            $estimation = $this->estimationService->createEstimation(
                $site->id,
                $owner,
                $request->user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Estimation created successfully',
                'data' => new EstimationResource($estimation),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get a single estimation with permission check.
     */
    public function show(Request $request, Estimation $estimation): JsonResponse
    {
        try {
            $estimation = $this->estimationService->getEstimation(
                $estimation->id,
                $request->user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Estimation retrieved successfully',
                'data' => new EstimationResource($estimation),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * List all estimations for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $estimations = $this->estimationService->listEstimations($request->user());

        return response()->json([
            'success' => true,
            'message' => 'Estimations retrieved successfully',
            'data' => EstimationResource::collection($estimations),
        ]);
    }

    /**
     * List all estimations for an organisation.
     * Members can view.
     */
    public function organisationIndex(Request $request, Organisation $organisation): JsonResponse
    {
        // Check if user belongs to organisation
        if (! $request->user()->belongsToOrganisation($organisation->id)) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have access to this organisation',
            ], 403);
        }

        $estimations = $this->estimationService->listEstimations($organisation);

        return response()->json([
            'success' => true,
            'message' => 'Organisation estimations retrieved successfully',
            'data' => EstimationResource::collection($estimations),
        ]);
    }

    /**
     * Recalculate an estimation.
     * Admin/owner only.
     */
    public function update(UpdateEstimationRequest $request, Estimation $estimation): JsonResponse
    {
        try {
            $updatedEstimation = $this->estimationService->updateEstimation(
                $estimation->id,
                $request->user()
            );

            return response()->json([
                'success' => true,
                'message' => 'Estimation recalculated successfully',
                'data' => new EstimationResource($updatedEstimation),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Soft delete an estimation.
     * Admin/owner only.
     */
    public function destroy(Request $request, Estimation $estimation): JsonResponse
    {
        try {
            // Verify user has write permission
            $this->estimationService->getEstimation($estimation->id, $request->user());

            // Soft delete
            $estimation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Estimation deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 403);
        }
    }
}
