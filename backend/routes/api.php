<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrganisationController;
use App\Http\Controllers\Api\SiteController;
use Illuminate\Support\Facades\Route;

// Public auth routes (no CSRF)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/register/installer', [AuthController::class, 'registerInstaller']);
    Route::post('/register/provider', [AuthController::class, 'registerProvider']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    Route::apiResource('sites', SiteController::class)->only(['index', 'store', 'show']);

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
    });
    Route::post('/invitations/accept', [OrganisationController::class, 'acceptInvitation']);
});
