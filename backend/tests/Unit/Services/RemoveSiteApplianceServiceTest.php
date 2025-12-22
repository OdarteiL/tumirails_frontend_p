<?php

namespace Tests\Unit\Services;

use App\Actions\Site\RemoveApplianceFromSiteAction;
use App\Services\SiteApplianceService;
use App\Models\Appliance;
use App\Models\Category;
use App\Models\Organisation;
use App\Models\Site;
use App\Models\SiteAppliance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RemoveSiteApplianceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_remove_appliance_for_user_site(): void
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

        $service = new SiteApplianceService(new \App\Actions\Site\AddApplianceToSiteAction(), new \App\Actions\Site\GetSiteAppliancesAction(), new RemoveApplianceFromSiteAction());

        $result = $service->removeAppliance($user->id, $site->id, $siteAppliance->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('site_appliances', ['id' => $siteAppliance->id]);
    }

    public function test_remove_appliance_for_organisation_site(): void
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();
        $site = Site::factory()->create(['owner_id' => $organisation->id, 'owner_type' => Organisation::class]);
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

        $service = new SiteApplianceService(new \App\Actions\Site\AddApplianceToSiteAction(), new \App\Actions\Site\GetSiteAppliancesAction(), new RemoveApplianceFromSiteAction());

        $result = $service->removeOrganisationSiteAppliance($user->id, $organisation->id, $site->id, $siteAppliance->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('site_appliances', ['id' => $siteAppliance->id]);
    }
}
