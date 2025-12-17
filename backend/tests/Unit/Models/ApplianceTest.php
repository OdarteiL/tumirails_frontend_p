<?php

namespace Tests\Unit\Models;

use App\Models\Appliance;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplianceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $otherUser;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->category = Category::factory()->create();
    }

    #[Test]
    public function public_scope_returns_only_public_appliances(): void
    {
        // Create public and private appliances
        Appliance::factory()->public()->count(3)->create(['category_id' => $this->category->id]);
        Appliance::factory()->private()->count(2)->create(['category_id' => $this->category->id]);

        $publicAppliances = Appliance::public()->get();

        $this->assertCount(3, $publicAppliances);
        $this->assertTrue($publicAppliances->every(fn ($appliance) => $appliance->is_public === true));
    }

    #[Test]
    public function active_scope_returns_only_active_appliances(): void
    {
        // Create active and inactive appliances
        Appliance::factory()->count(3)->create(['category_id' => $this->category->id, 'is_active' => true]);
        Appliance::factory()->count(2)->create(['category_id' => $this->category->id, 'is_active' => false]);

        $activeAppliances = Appliance::active()->get();

        $this->assertCount(3, $activeAppliances);
        $this->assertTrue($activeAppliances->every(fn ($appliance) => $appliance->is_active === true));
    }

    #[Test]
    public function owned_by_scope_returns_only_user_appliances(): void
    {
        // Create appliances owned by different users
        Appliance::factory()->count(3)->create([
            'category_id' => $this->category->id,
            'owner_id' => $this->user->id,
            'owner_type' => User::class,
        ]);

        Appliance::factory()->count(2)->create([
            'category_id' => $this->category->id,
            'owner_id' => $this->otherUser->id,
            'owner_type' => User::class,
        ]);

        $userAppliances = Appliance::ownedBy($this->user->id, User::class)->get();

        $this->assertCount(3, $userAppliances);
        $this->assertTrue($userAppliances->every(fn ($appliance) => $appliance->owner_id === $this->user->id));
    }

    #[Test]
    public function visible_to_scope_returns_public_and_user_private_appliances(): void
    {
        // Create public appliances
        Appliance::factory()->public()->count(2)->create(['category_id' => $this->category->id]);

        // Create user's private appliances
        Appliance::factory()->private()->count(3)->create([
            'category_id' => $this->category->id,
            'owner_id' => $this->user->id,
            'owner_type' => User::class,
        ]);

        // Create other user's private appliances (should not be visible)
        Appliance::factory()->private()->count(2)->create([
            'category_id' => $this->category->id,
            'owner_id' => $this->otherUser->id,
            'owner_type' => User::class,
        ]);

        $visibleAppliances = Appliance::visibleTo($this->user->id, User::class)->get();

        // Should see 2 public + 3 own private = 5 total
        $this->assertCount(5, $visibleAppliances);
    }

    #[Test]
    public function owner_relationship_returns_correct_user(): void
    {
        $appliance = Appliance::factory()->create([
            'category_id' => $this->category->id,
            'owner_id' => $this->user->id,
            'owner_type' => User::class,
        ]);

        $this->assertInstanceOf(User::class, $appliance->owner);
        $this->assertEquals($this->user->id, $appliance->owner->id);
    }

    #[Test]
    public function category_relationship_returns_correct_category(): void
    {
        $appliance = Appliance::factory()->create([
            'category_id' => $this->category->id,
        ]);

        $this->assertInstanceOf(Category::class, $appliance->category);
        $this->assertEquals($this->category->id, $appliance->category->id);
    }

    #[Test]
    public function setting_is_active_to_false_excludes_from_default_queries(): void
    {
        $appliance = Appliance::factory()->create([
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        // Should be visible by default
        $this->assertCount(1, Appliance::all());

        // Set to inactive
        $appliance->update(['is_active' => false]);

        // Should not be visible in default queries
        $this->assertCount(0, Appliance::all());
    }

    #[Test]
    public function with_inactive_scope_includes_inactive_appliances(): void
    {
        Appliance::factory()->count(3)->create([
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        Appliance::factory()->count(2)->create([
            'category_id' => $this->category->id,
            'is_active' => false,
        ]);

        // Default query should only return active
        $this->assertCount(3, Appliance::all());

        // With inactive scope should return all
        $this->assertCount(5, Appliance::withInactive()->get());
    }

    #[Test]
    public function factory_public_state_creates_public_appliance(): void
    {
        $appliance = Appliance::factory()->public()->create([
            'category_id' => $this->category->id,
        ]);

        $this->assertTrue($appliance->is_public);
    }

    #[Test]
    public function factory_private_state_creates_private_appliance(): void
    {
        $appliance = Appliance::factory()->private()->create([
            'category_id' => $this->category->id,
        ]);

        $this->assertFalse($appliance->is_public);
    }

    #[Test]
    public function factory_inactive_state_creates_inactive_appliance(): void
    {
        $appliance = Appliance::factory()->inactive()->create([
            'category_id' => $this->category->id,
        ]);

        $this->assertFalse($appliance->is_active);

        // Should not appear in default queries
        $this->assertCount(0, Appliance::all());
    }

    #[Test]
    public function efficiency_rating_accessor_returns_value_from_metadata(): void
    {
        $appliance = Appliance::factory()->create([
            'category_id' => $this->category->id,
            'metadata' => ['efficiency_rating' => 'A+'],
        ]);

        $this->assertEquals('A+', $appliance->efficiency_rating);
    }

    #[Test]
    public function efficiency_rating_accessor_returns_null_when_not_set(): void
    {
        $appliance = Appliance::factory()->create([
            'category_id' => $this->category->id,
            'metadata' => null,
        ]);

        $this->assertNull($appliance->efficiency_rating);
    }
}
