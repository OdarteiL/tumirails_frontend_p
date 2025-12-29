<?php

namespace Tests\Feature\Api;

use App\Models\Appliance;
use App\Models\Category;
use App\Models\Site;
use App\Models\SiteAppliance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RemoveSiteApplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_remove_site_appliance(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
        $category = Category::factory()->create(['user_id' => $user->id]);
        $appliance = Appliance::factory()->create(['category_id' => $category->id, 'owner_id' => $user->id, 'owner_type' => User::class]);

        $siteAppliance = SiteAppliance::create([
            'added_by_id' => $user->id,
            'added_by_type' => User::class,
            'site_id' => $site->id,
            'appliance_id' => $appliance->id,
            'quantity' => 1,
            'daily_usage_hours' => 2.0,
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/sites/{$site->id}/appliances/{$siteAppliance->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'message' => 'Appliance removed from site successfully']);

        $this->assertDatabaseMissing('site_appliances', ['id' => $siteAppliance->id]);
    }
}
