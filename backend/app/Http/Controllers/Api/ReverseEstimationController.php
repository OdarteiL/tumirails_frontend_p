<?php

namespace App\Http\Controllers\Api;

use App\Actions\Estimation\CalculateEnergyFromCostAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReverseEstimationRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class ReverseEstimationController extends Controller
{
    public function __invoke(
        ReverseEstimationRequest $request,
        CalculateEnergyFromCostAction $action
    ): JsonResponse {
        $validated = $request->validated();

        $amount = (float) $validated['amount'];
        $date = $request->has('date')
            ? Carbon::parse($validated['date'])
            : Carbon::now();

        $result = $action->execute($amount, $date);

        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => 'Reverse estimation calculated successfully',
        ]);
    }
}
