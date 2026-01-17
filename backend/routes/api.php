<?php

use App\Http\Controllers\Api\Admin\ApplianceController as AdminApplianceController;
use App\Http\Controllers\Api\ApplianceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\EstimationController;
use App\Http\Controllers\Api\FetchGuestEstimationController;
use App\Http\Controllers\Api\GuestEstimationController;
use App\Http\Controllers\Api\OrganisationController;
use App\Http\Controllers\Api\RecommendationController;
use App\Http\Controllers\Api\SiteController;
use Illuminate\Support\Facades\Route;

// Public auth routes (no CSRF)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/register/installer', [AuthController::class, 'registerInstaller']);
    Route::post('/register/provider', [AuthController::class, 'registerProvider']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Guest estimation
Route::post('/estimations/guest', GuestEstimationController::class)->middleware('throttle:10,1');
Route::get('/estimations/guest/{referenceCode}', FetchGuestEstimationController::class)->middleware('throttle:10,1');

// Contact form
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:10,1');

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // Admin appliance routes (must come before user routes)
    Route::middleware('isAdmin')->prefix('admin')->group(function () {
        Route::post('/appliances', [AdminApplianceController::class, 'store']);
        Route::put('/appliances/{appliance}', [AdminApplianceController::class, 'update']);
        Route::delete('/appliances/{appliance}', [AdminApplianceController::class, 'destroy']);

        Route::get('/contacts', [ContactController::class, 'index']);
        Route::get('/contacts/{contact}', [ContactController::class, 'show']);
    });

    // User appliance routes
    Route::apiResource('appliances', ApplianceController::class);

    Route::apiResource('sites', SiteController::class)->only(['index', 'store', 'show']);

    // Estimation routes
    Route::apiResource('estimations', EstimationController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
    Route::get('estimations/{estimation}/recommendations', [RecommendationController::class, 'index']);
    Route::post('estimations/{estimation}/recommendations', [RecommendationController::class, 'store']);
    Route::get('estimations/{estimation}/recommendation-bundles', [RecommendationController::class, 'bundles']);

    // Organisation routes
    Route::apiResource('organisations', OrganisationController::class);
    Route::prefix('organisations')->group(function () {
        Route::get('/{organisation}/members', [OrganisationController::class, 'members']);
        Route::post('/{organisation}/invite', [OrganisationController::class, 'inviteMember']);
        Route::patch('/{organisation}/members/{member}', [OrganisationController::class, 'updateMember']);
        Route::delete('/{organisation}/members/{member}', [OrganisationController::class, 'removeMember']);

        // Organisation sites
        Route::get('/{organisation}/sites', [SiteController::class, 'organisationIndex']);
        Route::post('/{organisation}/sites', [SiteController::class, 'organisationStore']);
        Route::get('/{organisation}/sites/{siteId}', [SiteController::class, 'organisationShow']);

        // Organisation site appliances
        Route::post('/{organisation}/sites/{siteId}/appliances', [SiteController::class, 'addApplianceToOrganisationSite']);
        Route::get('/{organisation}/sites/{siteId}/appliances', [SiteController::class, 'organisationAppliances']);
        Route::delete('/{organisation}/sites/{siteId}/appliances/{siteApplianceId}', [SiteController::class, 'organisationRemoveAppliance']);

        // Organisation estimations
        Route::get('/{organisation}/estimations', [EstimationController::class, 'organisationIndex']);
    });
    Route::post('/invitations/accept', [OrganisationController::class, 'acceptInvitation']);
    Route::post('/invitations/reject', [OrganisationController::class, 'rejectInvitation']);

    // User site appliances
    Route::post('sites/{site}/appliances', [SiteController::class, 'addAppliance']);
    Route::get('sites/{site}/appliances', [SiteController::class, 'appliances']);
    Route::delete('sites/{site}/appliances/{siteAppliance}', [SiteController::class, 'removeAppliance']);
});
