<?php

namespace Tests\Unit\Actions\Recommendation;

use App\Actions\Recommendation\GetRecommendationBundlesAction;
use App\Models\Estimation;
use App\Models\RecommendationBundle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetRecommendationBundlesActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_bundles_returns_created_bundles(): void
    {
        $user = User::factory()->create();
        $estimation = Estimation::factory()->create(['owner_type' => User::class, 'owner_id' => $user->id]);

        // Create two bundles
        RecommendationBundle::factory()->create(['estimation_id' => $estimation->id, 'total_cost' => 100]);
        RecommendationBundle::factory()->create(['estimation_id' => $estimation->id, 'total_cost' => 200]);

        $action = new GetRecommendationBundlesAction();

        $result = $action->execute($estimation);

        $this->assertCount(2, $result);
        $totals = $result->pluck('total_cost')->map(fn ($v) => (float) $v);
        $this->assertTrue($totals->contains(100.0));
        $this->assertTrue($totals->contains(200.0));
    }
}
