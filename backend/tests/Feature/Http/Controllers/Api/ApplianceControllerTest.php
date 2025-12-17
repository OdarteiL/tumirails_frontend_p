<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Appliance;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplianceControllerTest extends TestCase
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
    public function index_returns_public_and_user_private_appliances(): void
    {
        // Create public appliances
        Appliance::factory()->public()->count(3)->create(['category_id' => $this->category->id]);

        // Create user's private appliances
        Appliance::factory()->private()->count(2)->create([
            'category_id' => $this->category->id,
            'owner_id' => $this->user->id,
            'owner_type' => User::class,
        ]);

        // Create other user's private appliances (should not be returned)
        Appliance::factory()->private()->count(2)->create([
            'category_id' => $this->category->id,
            'owner_id' => $this->otherUser->id,
            'owner_type' => User::class,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/appliances');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'name', 'default_wattage', 'is_public', 'category'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ])
            ->assertJson(['success' => true]);

        // Should return 5 appliances (3 public + 2 user's private)
        $this->assertCount(5, $response->json('data'));
    }

    #[Test]
    public function index_does_not_return_other_users_private_appliances(): void
    {
        Appliance::factory()->private()->count(3)->create([
            'category_id' => $this->category->id,
            'owner_id' => $this->otherUser->id,
            'owner_type' => User::class,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/appliances');

        $response->assertOk();
        $this->assertCount(0, $response->json('data'));
    }

    #[Test]
    public function index_search_filter_works(): void
    {
        Appliance::factory()->public()->create([
            'category_id' => $this->category->id,
            'name' => 'LED Light Bulb',
        ]);

        Appliance::factory()->public()->create([
            'category_id' => $this->category->id,
            'name' => 'Ceiling Fan',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/appliances?search=LED');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('LED Light Bulb', $response->json('data.0.name'));
    }

    #[Test]
    public function index_category_filter_works(): void
    {
        $category2 = Category::factory()->create();

        Appliance::factory()->public()->count(2)->create(['category_id' => $this->category->id]);
        Appliance::factory()->public()->count(3)->create(['category_id' => $category2->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/appliances?category_id='.$this->category->id);

        $response->assertOk();
        $this->assertCount(2, $response->json('data'));
    }

    #[Test]
    public function index_pagination_works(): void
    {
        Appliance::factory()->public()->count(20)->create(['category_id' => $this->category->id]);

        $response = $this->actingAs($this->user)->getJson('/api/appliances');

        $response->assertOk()
            ->assertJsonStructure([
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);

        $this->assertEquals(15, $response->json('meta.per_page'));
        $this->assertEquals(20, $response->json('meta.total'));
        $this->assertEquals(2, $response->json('meta.last_page'));
    }

    #[Test]
    public function store_creates_private_appliance(): void
    {
        $data = [
            'name' => 'Custom LED Strip',
            'category_id' => $this->category->id,
            'default_wattage' => 20,
            'default_usage_hours' => 6,
            'metadata' => [
                'efficiency_rating' => 'A+',
                'notes' => 'RGB LED strip',
            ],
        ];

        $response = $this->actingAs($this->user)->postJson('/api/appliances', $data);

        $response->assertCreated()
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'name', 'default_wattage', 'is_public'],
                'message',
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Custom LED Strip',
                    'is_public' => false,
                ],
            ]);

        $this->assertDatabaseHas('appliances', [
            'name' => 'Custom LED Strip',
            'is_public' => false,
            'owner_id' => $this->user->id,
        ]);
    }

    #[Test]
    public function store_returns_validation_errors_for_invalid_data(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/appliances', [
            'name' => '', // Missing required name
            'category_id' => 999, // Invalid category
            'default_wattage' => -10, // Invalid wattage
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'error',
                'errors' => ['name', 'category_id', 'default_wattage'],
            ]);
    }

    #[Test]
    public function show_returns_public_appliance(): void
    {
        $appliance = Appliance::factory()->public()->create(['category_id' => $this->category->id]);

        $response = $this->actingAs($this->user)->getJson("/api/appliances/{$appliance->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => ['id' => $appliance->id],
            ]);
    }

    #[Test]
    public function show_returns_user_own_private_appliance(): void
    {
        $appliance = Appliance::factory()->private()->create([
            'category_id' => $this->category->id,
            'owner_id' => $this->user->id,
            'owner_type' => User::class,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/appliances/{$appliance->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => ['id' => $appliance->id],
            ]);
    }

    #[Test]
    public function show_returns_403_for_other_users_private_appliance(): void
    {
        $appliance = Appliance::factory()->private()->create([
            'category_id' => $this->category->id,
            'owner_id' => $this->otherUser->id,
            'owner_type' => User::class,
        ]);

        $response = $this->actingAs($this->user)->getJson("/api/appliances/{$appliance->id}");

        $response->assertForbidden()
            ->assertJson([
                'success' => false,
                'error' => 'You do not have permission to view this appliance',
            ]);
    }

    #[Test]
    public function update_allows_user_to_update_own_appliance(): void
    {
        $appliance = Appliance::factory()->private()->create([
            'category_id' => $this->category->id,
            'owner_id' => $this->user->id,
            'owner_type' => User::class,
            'name' => 'Original Name',
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/appliances/{$appliance->id}", [
            'name' => 'Updated Name',
            'default_usage_hours' => 8,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => ['name' => 'Updated Name'],
                'message' => 'Appliance updated successfully',
            ]);

        $this->assertDatabaseHas('appliances', [
            'id' => $appliance->id,
            'name' => 'Updated Name',
        ]);
    }

    #[Test]
    public function update_returns_403_for_other_users_appliance(): void
    {
        $appliance = Appliance::factory()->private()->create([
            'category_id' => $this->category->id,
            'owner_id' => $this->otherUser->id,
            'owner_type' => User::class,
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/appliances/{$appliance->id}", [
            'name' => 'Hacked Name',
        ]);

        $response->assertForbidden();
    }

    #[Test]
    public function update_prevents_changing_is_public_field(): void
    {
        $appliance = Appliance::factory()->private()->create([
            'category_id' => $this->category->id,
            'owner_id' => $this->user->id,
            'owner_type' => User::class,
        ]);

        $response = $this->actingAs($this->user)->putJson("/api/appliances/{$appliance->id}", [
            'is_public' => true,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['is_public']);
    }

    #[Test]
    public function destroy_allows_user_to_delete_own_appliance(): void
    {
        $appliance = Appliance::factory()->private()->create([
            'category_id' => $this->category->id,
            'owner_id' => $this->user->id,
            'owner_type' => User::class,
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/appliances/{$appliance->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Appliance deleted successfully',
            ]);

        // Verify soft delete (is_active = false)
        $this->assertDatabaseHas('appliances', [
            'id' => $appliance->id,
            'is_active' => false,
        ]);

        // Verify not in default query
        $this->assertNull(Appliance::find($appliance->id));
    }

    #[Test]
    public function destroy_returns_403_for_other_users_appliance(): void
    {
        $appliance = Appliance::factory()->private()->create([
            'category_id' => $this->category->id,
            'owner_id' => $this->otherUser->id,
            'owner_type' => User::class,
        ]);

        $response = $this->actingAs($this->user)->deleteJson("/api/appliances/{$appliance->id}");

        $response->assertForbidden();

        // Verify not deleted
        $this->assertDatabaseHas('appliances', [
            'id' => $appliance->id,
            'is_active' => true,
        ]);
    }

    #[Test]
    public function deleted_appliance_not_in_index_list(): void
    {
        $appliance = Appliance::factory()->private()->create([
            'category_id' => $this->category->id,
            'owner_id' => $this->user->id,
            'owner_type' => User::class,
        ]);

        // Verify it's in the list
        $response = $this->actingAs($this->user)->getJson('/api/appliances');
        $this->assertCount(1, $response->json('data'));

        // Delete it
        $this->actingAs($this->user)->deleteJson("/api/appliances/{$appliance->id}");

        // Verify it's not in the list anymore
        $response = $this->actingAs($this->user)->getJson('/api/appliances');
        $this->assertCount(0, $response->json('data'));
    }

    #[Test]
    public function unauthenticated_requests_return_401(): void
    {
        $response = $this->getJson('/api/appliances');
        $response->assertUnauthorized();

        $response = $this->postJson('/api/appliances', []);
        $response->assertUnauthorized();

        $appliance = Appliance::factory()->public()->create(['category_id' => $this->category->id]);

        $response = $this->getJson("/api/appliances/{$appliance->id}");
        $response->assertUnauthorized();

        $response = $this->putJson("/api/appliances/{$appliance->id}", []);
        $response->assertUnauthorized();

        $response = $this->deleteJson("/api/appliances/{$appliance->id}");
        $response->assertUnauthorized();
    }
}
