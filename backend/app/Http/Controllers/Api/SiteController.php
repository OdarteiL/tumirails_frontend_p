<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddApplianceToSiteRequest;
use App\Http\Requests\CreateSiteRequest;
use App\Http\Resources\SiteResource;
use App\Http\Resources\UserApplianceResource;
use App\Services\SiteApplianceService;
use App\Services\SiteService;
use Illuminate\Http\JsonResponse;

class SiteController extends Controller
{
    public function __construct(
        private readonly SiteService $siteService,
        private readonly SiteApplianceService $siteApplianceService
    ) {}

    public function index(): JsonResponse
    {
        $sites = $this->siteService->getUserSites(auth()->user());

        return response()->json([
            'success' => true,
            'message' => 'Sites retrieved successfully',
            'data' => SiteResource::collection($sites),
        ]);
    }

    public function store(CreateSiteRequest $request): JsonResponse
    {
        $site = $this->siteService->createSite([
            ...$request->validated(),
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'data' => new SiteResource($site),
            'message' => 'Site created successfully',
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        $site = $this->siteService->getSiteById($id, auth()->user());

        return response()->json([
            'success' => true,
            'data' => new SiteResource($site),
            'message' => 'Site retrieved successfully',
        ]);
    }

    public function addAppliance(int $siteId, AddApplianceToSiteRequest $request): JsonResponse
    {
        try {
            $userAppliance = $this->siteApplianceService->addAppliance(
                auth()->id(),
                $siteId,
                $request->validated('appliance_id'),
                $request->validated('quantity'),
                $request->validated('daily_usage_hours'),
                $request->validated('notes')
            );

            return response()->json([
                'success' => true,
                'data' => new UserApplianceResource($userAppliance),
                'message' => 'Appliance added to site successfully',
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 409);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Site not found or access denied',
            ], 404);
        }
    }
}
