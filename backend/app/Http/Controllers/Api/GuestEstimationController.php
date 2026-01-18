<?php

namespace App\Http\Controllers\Api;

use App\Actions\Estimation\StoreGuestEstimationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\GuestEstimationRequest;
use App\Http\Resources\GuestEstimationResource;

class GuestEstimationController extends Controller
{
    public function __invoke(GuestEstimationRequest $request, StoreGuestEstimationAction $storeGuestEstimationAction): GuestEstimationResource
    {
        $estimation = $storeGuestEstimationAction->execute($request->validated());

        return new GuestEstimationResource($estimation);
    }
}
