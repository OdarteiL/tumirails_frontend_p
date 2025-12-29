<?php

namespace App\Services;

use App\Actions\Recommendation\GenerateRecommendationsAction;
use App\Actions\Recommendation\GetRecommendationBundlesAction;
use App\Actions\Recommendation\PersistRecommendationBundleAction;
use App\Models\Estimation;

class RecommendationService
{
    protected GenerateRecommendationsAction $action;

    public function __construct(?GenerateRecommendationsAction $action = null)
    {
        $this->action = $action ?? new GenerateRecommendationsAction();
    }

    public function generateRecommendations(Estimation $estimation, array $options = []): array
    {
        $defaults = [
            'top_n' => 5,
            'beam' => 20,
            'single_provider_first' => true,
            'limit' => 5,
        ];

        $opts = array_merge($defaults, $options);

        return $this->action->execute($estimation, $opts);
    }

    /**
     * Persist a user-selected recommendation bundle.
     */
    public function saveBundle(Estimation $estimation, array $data, \App\Models\User $actor)
    {
        $action = new PersistRecommendationBundleAction();

        return $action->execute($estimation, $data, $actor);
    }

    /**
     * Retrieve persisted recommendation bundles for an estimation.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBundles(Estimation $estimation)
    {
        $action = new GetRecommendationBundlesAction();

        return $action->execute($estimation);
    }
}
