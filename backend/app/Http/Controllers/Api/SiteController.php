<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddApplianceToSiteRequest;
use App\Http\Requests\CreateSiteRequest;
use App\Http\Resources\SiteApplianceResource;
use App\Http\Resources\SiteResource;
use App\Models\Organisation;
use App\Services\OrganisationService;
use App\Services\SiteApplianceService;
use App\Services\SiteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function __construct(
        private readonly SiteService $siteService,
        private readonly OrganisationService $organisationService,
        private readonly SiteApplianceService $siteApplianceService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $sites = $this->siteService->getUserSites($request->user());

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
            'owner_id' => $request->user()->id,
            'owner_type' => \App\Models\User::class,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Site created successfully',
            'data' => new SiteResource($site),
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $site = $this->siteService->getSiteById($id, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Site retrieved successfully',
            'data' => new SiteResource($site),
        ]);
    }

    /**
     * Get all sites for an organisation.
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

        $sites = $this->siteService->getOrganisationSites($organisation);

        return response()->json([
            'success' => true,
            'message' => 'Organisation sites retrieved successfully',
            'data' => SiteResource::collection($sites),
        ]);
    }

    /**
     * Create a site for an organisation.
     */
    public function organisationStore(CreateSiteRequest $request, Organisation $organisation): JsonResponse
    {
        // Check if user has permission (owner or admin)
        if (! $this->organisationService->userHasPermission($request->user(), $organisation, 'admin')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to create sites for this organisation',
            ], 403);
        }

        $site = $this->siteService->createSite([
            ...$request->validated(),
            'owner_id' => $organisation->id,
            'owner_type' => Organisation::class,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Site created successfully',
            'data' => new SiteResource($site),
        ], 201);
    }

    /**
     * Get a specific organisation site.
     */
    public function organisationShow(Request $request, Organisation $organisation, int $siteId): JsonResponse
    {
        try {
            $site = $this->siteService->getOrganisationSiteById($siteId, $organisation, $request->user());

            return response()->json([
                'success' => true,
                'message' => 'Site retrieved successfully',
                'data' => new SiteResource($site),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException ? 404 : 403);
        }
    }

    public function addAppliance(int $siteId, AddApplianceToSiteRequest $request): JsonResponse
    {
        try {
            $siteAppliance = $this->siteApplianceService->addAppliance(
                auth()->id(),
                $siteId,
                $request->validated('appliance_id'),
                $request->validated('quantity'),
                $request->validated('daily_usage_hours'),
                $request->validated('notes')
            );

            return response()->json([
                'success' => true,
                'data' => new SiteApplianceResource($siteAppliance),
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

    /**
     * List appliances attached to a user-owned site.
     */
    public function appliances(Request $request, int $siteId): JsonResponse
    {
        try {
            $siteAppliances = $this->siteApplianceService->getSiteAppliances($request->user()->id, $siteId);

            return response()->json([
                'success' => true,
                'message' => 'Site appliances retrieved successfully',
                'data' => SiteApplianceResource::collection($siteAppliances),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Site not found or access denied',
            ], 404);
        }
    }

    /**
     * Add an appliance to an organisation site.
     */
    public function addApplianceToOrganisationSite(Organisation $organisation, int $siteId, AddApplianceToSiteRequest $request): JsonResponse
    {
        // Check if user has permission (owner or admin)
        if (! $this->organisationService->userHasPermission($request->user(), $organisation, 'admin')) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to add appliances to this organisation site',
            ], 403);
        }

        try {
            $siteAppliance = $this->siteApplianceService->addApplianceToOrganisationSite(
                $request->user()->id,
                $organisation->id,
                $siteId,
                $request->validated('appliance_id'),
                $request->validated('quantity'),
                $request->validated('daily_usage_hours'),
                $request->validated('notes')
            );

            return response()->json([
                'success' => true,
                'data' => new SiteApplianceResource($siteAppliance),
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

    /**
     * List appliances attached to an organisation site.
     */
    public function organisationAppliances(Request $request, Organisation $organisation, int $siteId): JsonResponse
    {
        // Check if user belongs to organisation
        if (! $request->user()->belongsToOrganisation($organisation->id)) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have access to this organisation',
            ], 403);
        }

        try {
            $siteAppliances = $this->siteApplianceService->getOrganisationSiteAppliances($request->user()->id, $organisation->id, $siteId);

            return response()->json([
                'success' => true,
                'message' => 'Organisation site appliances retrieved successfully',
                'data' => SiteApplianceResource::collection($siteAppliances),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Site not found or access denied',
            ], 404);
        }
    }

    /**
     * Remove a site appliance from a user-owned site.
     */
    public function removeAppliance(int $siteId, int $siteApplianceId): JsonResponse
    {
        try {
            $this->siteApplianceService->removeAppliance(auth()->id(), $siteId, $siteApplianceId);

            return response()->json([
                'success' => true,
                'message' => 'Appliance removed from site successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Site not found or access denied',
            ], 404);
        }
    }

    /**
     * Remove a site appliance from an organisation site.
     */
    public function organisationRemoveAppliance(Request $request, Organisation $organisation, int $siteId, int $siteApplianceId): JsonResponse
    {
        // Check if user belongs to organisation
        if (! $request->user()->belongsToOrganisation($organisation->id)) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have access to this organisation',
            ], 403);
        }

        try {
            $this->siteApplianceService->removeOrganisationSiteAppliance($request->user()->id, $organisation->id, $siteId, $siteApplianceId);

            return response()->json([
                'success' => true,
                'message' => 'Appliance removed from organisation site successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Site not found or access denied',
            ], 404);
        }
    }
}
