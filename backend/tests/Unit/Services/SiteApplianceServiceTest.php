<?php

namespace Tests\Unit\Services;

use App\Actions\Site\AddApplianceToSiteAction;
use App\Models\Appliance;
use App\Models\Category;
use App\Models\Site;
use App\Models\User;
use App\Models\UserAppliance;
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

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockAction = Mockery::mock(AddApplianceToSiteAction::class);
        $this->service = new SiteApplianceService($this->mockAction);
    }

    public function test_add_appliance_success(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['user_id' => $user->id]);
        $category = Category::factory()->create(['user_id' => $user->id]);
        $appliance = Appliance::factory()->create(['category_id' => $category->id, 'user_id' => $user->id]);

        $expectedUserAppliance = new UserAppliance([
            'user_id' => $user->id,
            'site_id' => $site->id,
            'appliance_id' => $appliance->id,
            'quantity' => 2,
            'daily_usage_hours' => 8.0,
        ]);

        $this->mockAction
            ->shouldReceive('execute')
            ->once()
            ->with($user->id, $site->id, $appliance->id, 2, 8.0, null)
            ->andReturn($expectedUserAppliance);

        $result = $this->service->addAppliance($user->id, $site->id, $appliance->id, 2, 8.0);

        $this->assertEquals($expectedUserAppliance, $result);
    }

    public function test_add_appliance_throws_exception_for_non_owned_site(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $site = Site::factory()->create(['user_id' => $otherUser->id]);

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
        $site = Site::factory()->create(['user_id' => $user->id]);
        $category = Category::factory()->create(['user_id' => $user->id]);
        $appliance = Appliance::factory()->create(['category_id' => $category->id, 'user_id' => $user->id]);

        $expectedUserAppliance = new UserAppliance([
            'user_id' => $user->id,
            'site_id' => $site->id,
            'appliance_id' => $appliance->id,
            'quantity' => 1,
            'daily_usage_hours' => 10.0,
            'notes' => 'Test notes',
        ]);

        $this->mockAction
            ->shouldReceive('execute')
            ->once()
            ->with($user->id, $site->id, $appliance->id, 1, 10.0, 'Test notes')
            ->andReturn($expectedUserAppliance);

        $result = $this->service->addAppliance($user->id, $site->id, $appliance->id, 1, 10.0, 'Test notes');

        $this->assertEquals($expectedUserAppliance, $result);
    }
}
