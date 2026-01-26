<?php

namespace Tests\Feature;

use App\Models\Estimation;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstimationFetchingTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_fetch_reverse_estimations()
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);

        $this->actingAs($user);

        // Create a reverse estimation (characterized by total_watts = 0 usually, but linked to user)
        $estimation = Estimation::factory()->create([
            'owner_id' => $user->id,
            'owner_type' => User::class,
            'site_id' => $site->id,
            'estimated_monthly_cost' => 500,
            'total_watts' => 0,
        ]);

        $response = $this->getJson('/api/estimations');

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $estimation->id])
            ->assertJsonFragment(['estimated_monthly_cost' => '500.00']);
    }

    public function test_guest_can_fetch_estimation_by_reference_code()
    {
        Estimation::factory()->create([
            'owner_id' => null,
            'owner_type' => null,
            'site_id' => null,
            'reference_code' => 'ABC123XY',
            'expires_at' => now()->addDay(),
            'estimated_monthly_cost' => 100,
            'total_watts' => 0,
        ]);

        $response = $this->getJson('/api/estimations/guest/ABC123XY');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.reference_code', 'ABC123XY')
            ->assertJsonPath('data.estimated_monthly_cost', '100.00');
    }
}
