<?php

namespace Tests\Feature\Http\Controllers\Api\Admin;

use App\Models\Appliance;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApplianceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $regularUser;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->regularUser = User::factory()->create(['role' => 'customer']);
        $this->category = Category::factory()->create();
    }

    #[Test]
    public function admin_can_create_public_appliance(): void
    {
        $data = [
            'name' => 'Industrial Freezer',
            'category_id' => $this->category->id,
            'default_wattage' => 350,
            'default_usage_hours' => 24,
            'metadata' => [
                'efficiency_rating' => 'B',
                'notes' => 'Commercial grade',
            ],
            'is_public' => true,
        ];

        $response = $this->actingAs($this->admin)->postJson('/api/admin/appliances', $data);

        $response->assertCreated()
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'name', 'is_public'],
                'message',
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Industrial Freezer',
                    'is_public' => true,
                ],
            ]);

        $this->assertDatabaseHas('appliances', [
            'name' => 'Industrial Freezer',
            'is_public' => true,
            'owner_id' => $this->admin->id,
        ]);
    }

    #[Test]
    public function non_admin_cannot_create_public_appliance(): void
    {
        $data = [
            'name' => 'Unauthorized Appliance',
            'category_id' => $this->category->id,
            'default_wattage' => 100,
            'is_public' => true,
        ];

        $response = $this->actingAs($this->regularUser)->postJson('/api/admin/appliances', $data);

        $response->assertForbidden()
            ->assertJson([
                'success' => false,
                'error' => 'Admin access required',
            ]);

        $this->assertDatabaseMissing('appliances', [
            'name' => 'Unauthorized Appliance',
        ]);
    }

    #[Test]
    public function admin_can_update_any_appliance(): void
    {
        $appliance = Appliance::factory()->private()->create([
            'category_id' => $this->category->id,
            'owner_id' => $this->regularUser->id,
            'owner_type' => User::class,
            'name' => 'Original Name',
        ]);

        $response = $this->actingAs($this->admin)->putJson("/api/admin/appliances/{$appliance->id}", [
            'name' => 'Admin Updated Name',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => ['name' => 'Admin Updated Name'],
                'message' => 'Appliance updated successfully',
            ]);

        $this->assertDatabaseHas('appliances', [
            'id' => $appliance->id,
            'name' => 'Admin Updated Name',
        ]);
    }

    #[Test]
    public function admin_can_change_is_public_flag(): void
    {
        $appliance = Appliance::factory()->private()->create([
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->admin)->putJson("/api/admin/appliances/{$appliance->id}", [
            'is_public' => true,
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => ['is_public' => true],
            ]);

        $this->assertDatabaseHas('appliances', [
            'id' => $appliance->id,
            'is_public' => true,
        ]);
    }

    #[Test]
    public function non_admin_cannot_update_appliances_via_admin_endpoint(): void
    {
        $appliance = Appliance::factory()->public()->create([
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->regularUser)
            ->putJson("/api/admin/appliances/{$appliance->id}", [
                'name' => 'Hacked Name',
            ]);

        $response->assertForbidden()
            ->assertJson([
                'success' => false,
                'error' => 'Admin access required',
            ]);
    }

    #[Test]
    public function admin_can_delete_any_appliance(): void
    {
        $appliance = Appliance::factory()->public()->create([
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->admin)->deleteJson("/api/admin/appliances/{$appliance->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Appliance deleted successfully',
            ]);

        $this->assertDatabaseHas('appliances', [
            'id' => $appliance->id,
            'is_active' => false,
        ]);
    }

    #[Test]
    public function non_admin_cannot_delete_appliances_via_admin_endpoint(): void
    {
        $appliance = Appliance::factory()->public()->create([
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->regularUser)
            ->deleteJson("/api/admin/appliances/{$appliance->id}");

        $response->assertForbidden();

        $this->assertDatabaseHas('appliances', [
            'id' => $appliance->id,
            'is_active' => true,
        ]);
    }

    #[Test]
    public function admin_endpoint_returns_validation_errors(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/admin/appliances', [
            'name' => '',
            'category_id' => 999,
            'default_wattage' => -10,
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'error',
                'errors',
            ]);
    }

    #[Test]
    public function admin_created_appliances_are_owned_by_admin(): void
    {
        $data = [
            'name' => 'Admin Catalog Item',
            'category_id' => $this->category->id,
            'default_wattage' => 200,
            'is_public' => true,
        ];

        $this->actingAs($this->admin)->postJson('/api/admin/appliances', $data);

        $this->assertDatabaseHas('appliances', [
            'name' => 'Admin Catalog Item',
            'is_public' => true,
            'owner_id' => $this->admin->id,
            'owner_type' => User::class,
        ]);
    }
}
