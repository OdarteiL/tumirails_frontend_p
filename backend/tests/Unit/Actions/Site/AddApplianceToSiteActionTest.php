<?php

namespace Tests\Unit\Actions\Site;

use App\Actions\Site\AddApplianceToSiteAction;
use App\Models\Appliance;
use App\Models\Category;
use App\Models\Site;
use App\Models\SiteAppliance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddApplianceToSiteActionTest extends TestCase
{
    use RefreshDatabase;

    private AddApplianceToSiteAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new AddApplianceToSiteAction();
    }

    public function test_execute_creates_user_appliance_successfully(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
        $category = Category::factory()->create(['user_id' => $user->id]);
        $appliance = Appliance::factory()->create(['category_id' => $category->id, 'owner_id' => $user->id, 'owner_type' => User::class]);

        $result = $this->action->execute(
            $user->id,
            User::class,
            $site->id,
            $appliance->id,
            2,
            8.5,
            'Test notes'
        );

        $this->assertInstanceOf(SiteAppliance::class, $result);
        $this->assertEquals($user->id, $result->added_by_id);
        $this->assertEquals(User::class, $result->added_by_type);
        $this->assertEquals($site->id, $result->site_id);
        $this->assertEquals($appliance->id, $result->appliance_id);
        $this->assertEquals(2, $result->quantity);
        $this->assertEquals(8.5, $result->daily_usage_hours);
        $this->assertEquals('Test notes', $result->notes);
    }

    public function test_execute_throws_exception_for_duplicate_appliance(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
        $category = Category::factory()->create(['user_id' => $user->id]);
        $appliance = Appliance::factory()->create(['category_id' => $category->id, 'owner_id' => $user->id, 'owner_type' => User::class]);

        // Create first appliance
        $this->action->execute($user->id, User::class, $site->id, $appliance->id, 1, 5.0);

        // Try to create duplicate
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Appliance already added to this site');

        $this->action->execute($user->id, User::class, $site->id, $appliance->id, 2, 6.0);
    }

    public function test_execute_works_without_notes(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
        $category = Category::factory()->create(['user_id' => $user->id]);
        $appliance = Appliance::factory()->create(['category_id' => $category->id, 'owner_id' => $user->id, 'owner_type' => User::class]);

        $result = $this->action->execute($user->id, User::class, $site->id, $appliance->id, 1, 12.0);

        $this->assertNull($result->notes);
    }
}
