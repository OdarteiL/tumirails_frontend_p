<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GuestEstimationResource;
use App\Models\Estimation;

class FetchGuestEstimationController extends Controller
{
    public function __invoke(string $referenceCode): GuestEstimationResource
    {
        $estimation = Estimation::where('reference_code', $referenceCode)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        return new GuestEstimationResource($estimation);
    }
}
