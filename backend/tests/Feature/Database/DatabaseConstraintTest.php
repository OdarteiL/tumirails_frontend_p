<?php

namespace Tests\Feature\Database;

use App\Models\Appliance;
use App\Models\Category;
use App\Models\Site;
use App\Models\SiteAppliance;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseConstraintTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_appliances_cascade_delete_from_sites(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
        $category = Category::factory()->create(['user_id' => $user->id]);
        $appliance = Appliance::factory()->create(['category_id' => $category->id, 'owner_id' => $user->id, 'owner_type' => User::class]);

        $siteAppliance = SiteAppliance::factory()->create([
            'added_by_id' => $user->id,
            'added_by_type' => User::class,
            'site_id' => $site->id,
            'appliance_id' => $appliance->id,
        ]);

        $this->assertDatabaseHas('site_appliances', ['id' => $siteAppliance->id]);

        // Delete site should cascade delete site_appliances
        $site->delete();

        $this->assertDatabaseMissing('site_appliances', ['id' => $siteAppliance->id]);
    }

    public function test_site_appliances_cascade_delete_from_appliances(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
        $category = Category::factory()->create(['user_id' => $user->id]);
        $appliance = Appliance::factory()->create(['category_id' => $category->id, 'owner_id' => $user->id, 'owner_type' => User::class]);

        $siteAppliance = SiteAppliance::factory()->create([
            'added_by_id' => $user->id,
            'added_by_type' => User::class,
            'site_id' => $site->id,
            'appliance_id' => $appliance->id,
        ]);

        $this->assertDatabaseHas('site_appliances', ['id' => $siteAppliance->id]);

        // Delete appliance should cascade delete site_appliances
        $appliance->delete();

        $this->assertDatabaseMissing('site_appliances', ['id' => $siteAppliance->id]);
    }

    public function test_unique_constraint_on_site_id_appliance_id(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);
        $category = Category::factory()->create(['user_id' => $user->id]);
        $appliance = Appliance::factory()->create(['category_id' => $category->id, 'owner_id' => $user->id, 'owner_type' => User::class]);

        // Create first site appliance
        SiteAppliance::create([
            'added_by_id' => $user->id,
            'added_by_type' => User::class,
            'site_id' => $site->id,
            'appliance_id' => $appliance->id,
            'quantity' => 1,
            'daily_usage_hours' => 5.0,
        ]);

        // Try to create duplicate - should fail
        $this->expectException(QueryException::class);

        SiteAppliance::create([
            'added_by_id' => $user->id,
            'added_by_type' => User::class,
            'site_id' => $site->id,
            'appliance_id' => $appliance->id,
            'quantity' => 2,
            'daily_usage_hours' => 8.0,
        ]);
    }

    public function test_foreign_key_constraint_prevents_orphaned_records(): void
    {
        $this->expectException(QueryException::class);

        // Try to create site_appliance with non-existent site_id
        SiteAppliance::create([
            'added_by_id' => 1,
            'added_by_type' => User::class,
            'site_id' => 999,
            'appliance_id' => 1,
            'quantity' => 1,
            'daily_usage_hours' => 5.0,
        ]);
    }

    public function test_appliances_cascade_delete_from_categories(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);
        $appliance = Appliance::factory()->create(['category_id' => $category->id, 'owner_id' => $user->id, 'owner_type' => User::class]);

        $this->assertDatabaseHas('appliances', ['id' => $appliance->id]);

        // Delete category should cascade delete appliances
        $category->delete();

        $this->assertDatabaseMissing('appliances', ['id' => $appliance->id]);
    }

    public function test_categories_cascade_delete_from_users(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        $this->assertDatabaseHas('categories', ['id' => $category->id]);

        // Delete user should cascade delete categories
        $user->delete();

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
