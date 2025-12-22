<?php

namespace Tests\Unit\Actions\Site;

use App\Actions\Site\GetSiteAppliancesAction;
use App\Models\Appliance;
use App\Models\Category;
use App\Models\Site;
use App\Models\SiteAppliance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetSiteAppliancesActionTest extends TestCase
{
    use RefreshDatabase;

    private GetSiteAppliancesAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new GetSiteAppliancesAction();
    }

    public function test_execute_returns_site_appliances_with_relations(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);

        $category = Category::factory()->create(['user_id' => $user->id]);
        $appliance1 = Appliance::factory()->create(['category_id' => $category->id, 'owner_id' => $user->id, 'owner_type' => User::class]);
        $appliance2 = Appliance::factory()->create(['category_id' => $category->id, 'owner_id' => $user->id, 'owner_type' => User::class]);

        SiteAppliance::create([
            'added_by_id' => $user->id,
            'added_by_type' => User::class,
            'site_id' => $site->id,
            'appliance_id' => $appliance1->id,
            'quantity' => 1,
            'daily_usage_hours' => 4.0,
        ]);

        SiteAppliance::create([
            'added_by_id' => $user->id,
            'added_by_type' => User::class,
            'site_id' => $site->id,
            'appliance_id' => $appliance2->id,
            'quantity' => 2,
            'daily_usage_hours' => 6.0,
        ]);

        $result = $this->action->execute($site);

        $this->assertCount(2, $result);
        $this->assertTrue($result->first()->relationLoaded('appliance'));

        $ids = $result->pluck('appliance.id')->sort()->values()->all();
        $expected = collect([$appliance1->id, $appliance2->id])->sort()->values()->all();
        $this->assertEquals($expected, $ids);
    }
}
