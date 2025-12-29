<?php

namespace App\Services;

use App\Actions\Recommendation\GenerateRecommendationsAction;
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
}
