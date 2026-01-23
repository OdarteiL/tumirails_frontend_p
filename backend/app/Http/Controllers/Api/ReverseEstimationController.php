<?php

namespace App\Http\Controllers\Api;

use App\Actions\Estimation\ReverseEstimationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReverseEstimationRequest;
use Illuminate\Http\JsonResponse;

class ReverseEstimationController extends Controller
{
    public function __invoke(
        ReverseEstimationRequest $request,
        ReverseEstimationAction $action
    ): JsonResponse {
        $result = $action->execute($request->validated());

        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => 'Reverse estimation calculated successfully',
        ]);
    }
}
