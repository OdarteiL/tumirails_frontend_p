<?php

namespace App\Http\Controllers\Api;

use App\Actions\Estimation\CalculateGuestEstimationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\GuestEstimationRequest;
use App\Http\Resources\GuestEstimationResource;

class GuestEstimationController extends Controller
{
    public function __invoke(GuestEstimationRequest $request, CalculateGuestEstimationAction $calculateGuestEstimationAction): GuestEstimationResource
    {
        $estimation = $calculateGuestEstimationAction->execute($request->validated()['appliances']);

        return new GuestEstimationResource($estimation);
    }
}
