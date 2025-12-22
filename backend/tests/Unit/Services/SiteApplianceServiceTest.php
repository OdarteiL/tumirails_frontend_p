<?php

namespace Tests\Unit\Services;

use App\Actions\Site\AddApplianceToSiteAction;
use App\Actions\Site\GetSiteAppliancesAction;
use App\Actions\Site\RemoveApplianceFromSiteAction;
use App\Models\Appliance;
use App\Models\Category;
use App\Models\Site;
use App\Models\SiteAppliance;
use App\Models\User;
use App\Services\SiteApplianceService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SiteApplianceServiceTest extends TestCase
{
    use RefreshDatabase;

    private SiteApplianceService $service;

    private AddApplianceToSiteAction $mockAction;
    private RemoveApplianceFromSiteAction $mockRemoveAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockAction = Mockery::mock(AddApplianceToSiteAction::class);
        $this->mockRemoveAction = Mockery::mock(RemoveApplianceFromSiteAction::class);
        $this->service = new SiteApplianceService($this->mockAction, new GetSiteAppliancesAction(), $this->mockRemoveAction);
    }

    public function test_add_appliance_success(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
        $category = Category::factory()->create(['user_id' => $user->id]);
        $appliance = Appliance::factory()->create(['category_id' => $category->id, 'owner_id' => $user->id, 'owner_type' => User::class]);

        $expectedSiteAppliance = new SiteAppliance([
            'added_by_id' => $user->id,
            'added_by_type' => User::class,
            'site_id' => $site->id,
            'appliance_id' => $appliance->id,
            'quantity' => 2,
            'daily_usage_hours' => 8.0,
        ]);

        $this->mockAction
            ->shouldReceive('execute')
            ->once()
            ->with($user->id, User::class, $site->id, $appliance->id, 2, 8.0, null)
            ->andReturn($expectedSiteAppliance);

        $result = $this->service->addAppliance($user->id, $site->id, $appliance->id, 2, 8.0);

        $this->assertEquals($expectedSiteAppliance, $result);
    }

    public function test_add_appliance_throws_exception_for_non_owned_site(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $site = Site::factory()->create(['owner_id' => $otherUser->id, 'owner_type' => User::class]);

        $this->expectException(ModelNotFoundException::class);

        $this->service->addAppliance($user->id, $site->id, 1, 1, 5.0);
    }

    public function test_add_appliance_throws_exception_for_non_existent_site(): void
    {
        $user = User::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        $this->service->addAppliance($user->id, 999, 1, 1, 5.0);
    }

    public function test_add_appliance_with_notes(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
        $category = Category::factory()->create(['user_id' => $user->id]);
        $appliance = Appliance::factory()->create(['category_id' => $category->id, 'owner_id' => $user->id, 'owner_type' => User::class]);

        $expectedSiteAppliance = new SiteAppliance([
            'added_by_id' => $user->id,
            'added_by_type' => User::class,
            'site_id' => $site->id,
            'appliance_id' => $appliance->id,
            'quantity' => 1,
            'daily_usage_hours' => 10.0,
            'notes' => 'Test notes',
        ]);

        $this->mockAction
            ->shouldReceive('execute')
            ->once()
            ->with($user->id, User::class, $site->id, $appliance->id, 1, 10.0, 'Test notes')
            ->andReturn($expectedSiteAppliance);

        $result = $this->service->addAppliance($user->id, $site->id, $appliance->id, 1, 10.0, 'Test notes');

        $this->assertEquals($expectedSiteAppliance, $result);
    }

    public function test_get_site_appliances_for_user_owned_site(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);

        $category = Category::factory()->create(['user_id' => $user->id]);
        $appliance = Appliance::factory()->create(['category_id' => $category->id, 'owner_id' => $user->id, 'owner_type' => User::class]);

        SiteAppliance::create([
            'added_by_id' => $user->id,
            'added_by_type' => User::class,
            'site_id' => $site->id,
            'appliance_id' => $appliance->id,
            'quantity' => 3,
            'daily_usage_hours' => 2.5,
        ]);

        $result = $this->service->getSiteAppliances($user->id, $site->id);

        $this->assertCount(1, $result);
        $this->assertEquals($appliance->id, $result->first()->appliance->id);
    }

    public function test_get_organisation_site_appliances(): void
    {
        $user = User::factory()->create();
        $organisation = \App\Models\Organisation::factory()->create();
        $site = Site::factory()->create(['owner_id' => $organisation->id, 'owner_type' => \App\Models\Organisation::class]);

        $category = Category::factory()->create(['user_id' => $user->id]);
        $appliance = Appliance::factory()->create(['category_id' => $category->id, 'owner_id' => $user->id, 'owner_type' => User::class]);

        SiteAppliance::create([
            'added_by_id' => $user->id,
            'added_by_type' => User::class,
            'site_id' => $site->id,
            'appliance_id' => $appliance->id,
            'quantity' => 1,
            'daily_usage_hours' => 1.0,
        ]);

        $result = $this->service->getOrganisationSiteAppliances($user->id, $organisation->id, $site->id);

        $this->assertCount(1, $result);
        $this->assertEquals($appliance->id, $result->first()->appliance->id);
    }
}
