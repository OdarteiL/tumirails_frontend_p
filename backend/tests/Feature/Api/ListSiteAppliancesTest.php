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

class ListSiteAppliancesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->site = Site::factory()->create(['owner_id' => $this->user->id, 'owner_type' => User::class]);
        $category = Category::factory()->create(['user_id' => $this->user->id]);

        $appliance1 = Appliance::factory()->create(['category_id' => $category->id, 'owner_id' => $this->user->id, 'owner_type' => User::class]);
        $appliance2 = Appliance::factory()->create(['category_id' => $category->id, 'owner_id' => $this->user->id, 'owner_type' => User::class]);

        SiteAppliance::create([
            'added_by_id' => $this->user->id,
            'added_by_type' => User::class,
            'site_id' => $this->site->id,
            'appliance_id' => $appliance1->id,
            'quantity' => 1,
            'daily_usage_hours' => 4.0,
        ]);

        SiteAppliance::create([
            'added_by_id' => $this->user->id,
            'added_by_type' => User::class,
            'site_id' => $this->site->id,
            'appliance_id' => $appliance2->id,
            'quantity' => 2,
            'daily_usage_hours' => 6.0,
        ]);

        Sanctum::actingAs($this->user);
    }

    public function test_list_site_appliances_returns_items(): void
    {
        $response = $this->getJson("/api/sites/{$this->site->id}/appliances");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    [
                        'id',
                        'added_by_id',
                        'site_id',
                        'appliance_id',
                        'appliance' => ['id','name','default_wattage'],
                        'quantity',
                        'daily_usage_hours',
                    ]
                ]
            ]);

        $json = $response->json();
        $this->assertCount(2, $json['data']);
    }
}
