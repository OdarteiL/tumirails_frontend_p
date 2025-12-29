<?php

namespace App\Actions\Recommendation;

use App\Models\Estimation;
use App\Models\RecommendationBundle;
use App\Models\RecommendationBundleComponent;
use App\Models\User;

class PersistRecommendationBundleAction
{
    /**
     * Persist a recommendation bundle with components.
     * $data expected keys: total_cost, currency, components[] each with hardware_id, quantity, total_cost, role, rationale
     */
    public function execute(Estimation $estimation, array $data, User $actor): RecommendationBundle
    {
        $bundle = RecommendationBundle::create([
            'estimation_id' => $estimation->id,
            'owner_type' => $estimation->owner_type,
            'owner_id' => $estimation->owner_id,
            'total_cost' => $data['total_cost'] ?? 0,
            'currency' => $data['currency'] ?? 'GHS',
            'metadata' => $data['metadata'] ?? null,
            'created_by' => $actor->id,
        ]);

        foreach (($data['components'] ?? []) as $comp) {
            RecommendationBundleComponent::create([
                'bundle_id' => $bundle->id,
                'hardware_id' => $comp['hardware_id'],
                'role' => $comp['role'] ?? null,
                'quantity' => $comp['quantity'] ?? 1,
                'total_cost' => $comp['total_cost'] ?? 0,
                'rationale' => $comp['rationale'] ?? null,
            ]);
        }

        return $bundle->fresh('components');
    }
}
