<?php

namespace App\Actions\Recommendation;

use App\Models\Estimation;
use App\Models\RecommendationBundle;

class GetRecommendationBundlesAction
{
    /**
     * Return persisted recommendation bundles for an estimation.
     * Includes components and hardware relations for convenience.
     *
     * @return \Illuminate\Database\Eloquent\Collection|RecommendationBundle[]
     */
    public function execute(Estimation $estimation)
    {
        return RecommendationBundle::where('estimation_id', $estimation->id)
            ->with('components.hardware.hardwareType')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
