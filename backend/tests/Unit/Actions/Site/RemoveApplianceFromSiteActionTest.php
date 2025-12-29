<?php

namespace Tests\Unit\Actions\Site;

use App\Actions\Site\RemoveApplianceFromSiteAction;
use App\Models\Appliance;
use App\Models\Category;
use App\Models\Site;
use App\Models\SiteAppliance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemoveApplianceFromSiteActionTest extends TestCase
{
    use RefreshDatabase;

    private RemoveApplianceFromSiteAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new RemoveApplianceFromSiteAction();
    }

    public function test_execute_deletes_site_appliance(): void
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

        $result = $this->action->execute($siteAppliance->id, $site->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('site_appliances', ['id' => $siteAppliance->id]);
    }

    public function test_execute_throws_when_mismatch_site(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
        $otherSite = Site::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
        $category = Category::factory()->create(['user_id' => $user->id]);
        $appliance = Appliance::factory()->create(['category_id' => $category->id, 'owner_id' => $user->id, 'owner_type' => User::class]);

        $siteAppliance = SiteAppliance::create([
            'added_by_id' => $user->id,
            'added_by_type' => User::class,
            'site_id' => $otherSite->id,
            'appliance_id' => $appliance->id,
            'quantity' => 1,
            'daily_usage_hours' => 2.0,
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->action->execute($siteAppliance->id, $site->id);
    }
}
