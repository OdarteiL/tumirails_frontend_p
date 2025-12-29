<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRecommendationBundleRequest;
use App\Http\Resources\RecommendationBundleResource;
use App\Http\Resources\RecommendationResource;
use App\Models\Estimation;
use App\Models\Organisation;
use App\Models\User;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;

class RecommendationController extends Controller
{
    public function __construct(private RecommendationService $recommendationService) {}

    public function index(Estimation $estimation): JsonResponse
    {
        // Check authorization
        $user = auth()->user();
        $canAccess = false;

        // Check if user owns the estimation directly
        if ($estimation->owner_type === User::class && $estimation->owner_id === $user->id) {
            $canAccess = true;
        }

        // Check if user is member of organization that owns the estimation
        if ($estimation->owner_type === Organisation::class) {
            $organisation = Organisation::find($estimation->owner_id);
            if ($organisation && $organisation->members()->where('user_id', $user->id)->exists()) {
                $canAccess = true;
            }
        }

        if (! $canAccess) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $recommendations = $this->recommendationService->generateRecommendations($estimation);

        return response()->json([
            'success' => true,
            'data' => new RecommendationResource($estimation, $recommendations),
        ]);
    }

    public function bundles(Estimation $estimation): JsonResponse
    {
        $user = auth()->user();
        $canAccess = false;

        if ($estimation->owner_type === User::class && $estimation->owner_id === $user->id) {
            $canAccess = true;
        }

        if ($estimation->owner_type === Organisation::class) {
            $organisation = Organisation::find($estimation->owner_id);
            if ($organisation && $organisation->members()->where('user_id', $user->id)->exists()) {
                $canAccess = true;
            }
        }

        if (! $canAccess) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $bundles = $this->recommendationService->getBundles($estimation);

        return response()->json(['success' => true, 'data' => \App\Http\Resources\RecommendationBundleResource::collection($bundles)]);
    }

    public function store(StoreRecommendationBundleRequest $request, Estimation $estimation): JsonResponse
    {
        $user = auth()->user();

        // Authorization: allow owner user or organisation member
        $canAccess = false;
        if ($estimation->owner_type === User::class && $estimation->owner_id === $user->id) {
            $canAccess = true;
        }

        if ($estimation->owner_type === Organisation::class) {
            $organisation = Organisation::find($estimation->owner_id);
            if ($organisation && $organisation->members()->where('user_id', $user->id)->exists()) {
                $canAccess = true;
            }
        }

        if (! $canAccess) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $data = $request->validated();

        $bundle = $this->recommendationService->saveBundle($estimation, $data, $user);

        $bundle->load('components.hardware.hardwareType');

        return response()->json(['success' => true, 'data' => new RecommendationBundleResource($bundle)], 201);
    }
}
