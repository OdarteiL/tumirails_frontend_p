<?php

namespace Tests\Unit\Services;

use App\Actions\Appliance\CreateApplianceAction;
use App\Actions\Appliance\DeleteApplianceAction;
use App\Actions\Appliance\GetAppliancesAction;
use App\Actions\Appliance\UpdateApplianceAction;
use App\Models\Appliance;
use App\Models\Category;
use App\Models\User;
use App\Services\ApplianceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplianceServiceTest extends TestCase
{
    use RefreshDatabase;

    private ApplianceService $service;

    private User $user;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ApplianceService(
            new GetAppliancesAction(),
            new CreateApplianceAction(),
            new UpdateApplianceAction(),
            new DeleteApplianceAction()
        );

        $this->user = User::factory()->create();
        $this->category = Category::factory()->create();
    }

    #[Test]
    public function get_visible_appliances_returns_paginated_results(): void
    {
        Appliance::factory()->public()->count(5)->create(['category_id' => $this->category->id]);

        $result = $this->service->getVisibleAppliances($this->user->id, User::class);

        $this->assertCount(5, $result->items());
        $this->assertEquals(5, $result->total());
    }

    #[Test]
    public function get_visible_appliances_filters_by_category(): void
    {
        $category2 = Category::factory()->create();

        Appliance::factory()->public()->count(3)->create(['category_id' => $this->category->id]);
        Appliance::factory()->public()->count(2)->create(['category_id' => $category2->id]);

        $result = $this->service->getVisibleAppliances(
            $this->user->id,
            User::class,
            $this->category->id
        );

        $this->assertCount(3, $result->items());
    }

    #[Test]
    public function get_visible_appliances_filters_by_search(): void
    {
        Appliance::factory()->public()->create([
            'category_id' => $this->category->id,
            'name' => 'LED Light',
        ]);
        Appliance::factory()->public()->create([
            'category_id' => $this->category->id,
            'name' => 'Ceiling Fan',
        ]);

        $result = $this->service->getVisibleAppliances(
            $this->user->id,
            User::class,
            null,
            'LED'
        );

        $this->assertCount(1, $result->items());
        $this->assertEquals('LED Light', $result->items()[0]->name);
    }

    #[Test]
    public function create_appliance_creates_new_appliance(): void
    {
        $data = [
            'name' => 'Test Appliance',
            'category_id' => $this->category->id,
            'default_wattage' => 100,
            'default_usage_hours' => 8,
            'metadata' => ['efficiency_rating' => 'A'],
        ];

        $appliance = $this->service->createAppliance(
            $this->user->id,
            User::class,
            $data,
            false
        );

        $this->assertInstanceOf(Appliance::class, $appliance);
        $this->assertEquals('Test Appliance', $appliance->name);
        $this->assertEquals($this->user->id, $appliance->owner_id);
        $this->assertFalse($appliance->is_public);
    }

    #[Test]
    public function update_appliance_updates_existing_appliance(): void
    {
        $appliance = Appliance::factory()->create([
            'category_id' => $this->category->id,
            'name' => 'Original Name',
        ]);

        $updated = $this->service->updateAppliance($appliance, [
            'name' => 'Updated Name',
        ]);

        $this->assertEquals('Updated Name', $updated->name);
    }

    #[Test]
    public function delete_appliance_soft_deletes_appliance(): void
    {
        $appliance = Appliance::factory()->create([
            'category_id' => $this->category->id,
        ]);

        $result = $this->service->deleteAppliance($appliance);

        $this->assertTrue($result);
        $this->assertDatabaseHas('appliances', [
            'id' => $appliance->id,
            'is_active' => false,
        ]);
    }

    #[Test]
    public function can_view_returns_true_for_public_appliance(): void
    {
        $appliance = Appliance::factory()->public()->create([
            'category_id' => $this->category->id,
        ]);

        $canView = $this->service->canView($appliance, $this->user->id, User::class);

        $this->assertTrue($canView);
    }

    #[Test]
    public function can_view_returns_true_for_owned_private_appliance(): void
    {
        $appliance = Appliance::factory()->private()->create([
            'category_id' => $this->category->id,
            'owner_id' => $this->user->id,
            'owner_type' => User::class,
        ]);

        $canView = $this->service->canView($appliance, $this->user->id, User::class);

        $this->assertTrue($canView);
    }

    #[Test]
    public function can_view_returns_false_for_other_users_private_appliance(): void
    {
        $otherUser = User::factory()->create();
        $appliance = Appliance::factory()->private()->create([
            'category_id' => $this->category->id,
            'owner_id' => $otherUser->id,
            'owner_type' => User::class,
        ]);

        $canView = $this->service->canView($appliance, $this->user->id, User::class);

        $this->assertFalse($canView);
    }

    #[Test]
    public function is_owner_returns_true_for_owner(): void
    {
        $appliance = Appliance::factory()->create([
            'category_id' => $this->category->id,
            'owner_id' => $this->user->id,
            'owner_type' => User::class,
        ]);

        $isOwner = $this->service->isOwner($appliance, $this->user->id, User::class);

        $this->assertTrue($isOwner);
    }

    #[Test]
    public function is_owner_returns_false_for_non_owner(): void
    {
        $otherUser = User::factory()->create();
        $appliance = Appliance::factory()->create([
            'category_id' => $this->category->id,
            'owner_id' => $otherUser->id,
            'owner_type' => User::class,
        ]);

        $isOwner = $this->service->isOwner($appliance, $this->user->id, User::class);

        $this->assertFalse($isOwner);
    }
}
