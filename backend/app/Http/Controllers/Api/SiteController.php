<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSiteRequest;
use App\Http\Resources\SiteResource;
use App\Services\SiteService;
use Illuminate\Http\JsonResponse;

class SiteController extends Controller
{
    public function __construct(private readonly SiteService $siteService) {}

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
}
