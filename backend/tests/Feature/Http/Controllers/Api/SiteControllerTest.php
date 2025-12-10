<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_user_sites(): void
    {
        $user = User::factory()->create();
        Site::factory()->count(3)->create(['owner_id' => $user->id, 'owner_type' => User::class]);
        Site::factory()->count(2)->create(); // Other user's sites

        $response = $this->actingAs($user)->getJson('/api/sites');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_store_creates_site_successfully(): void
    {
        $user = User::factory()->create();
        $data = [
            'name' => 'New Site',
            'address' => '456 New St',
            'latitude' => 34.0522,
            'longitude' => -118.2437,
            'timezone' => 'America/Los_Angeles',
            'notes' => 'New site notes',
        ];

        $response = $this->actingAs($user)->postJson('/api/sites', $data);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Site created successfully',
            ])
            ->assertJsonPath('data.name', 'New Site');

        $this->assertDatabaseHas('sites', [
            'name' => 'New Site',
            'owner_id' => $user->id,
            'owner_type' => User::class,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/sites', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'address', 'latitude', 'longitude', 'timezone']);
    }

    public function test_store_validates_latitude_range(): void
    {
        $user = User::factory()->create();
        $data = [
            'name' => 'Test Site',
            'address' => '123 Test St',
            'latitude' => 91, // Invalid
            'longitude' => 0,
            'timezone' => 'UTC',
        ];

        $response = $this->actingAs($user)->postJson('/api/sites', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['latitude']);
    }

    public function test_store_validates_longitude_range(): void
    {
        $user = User::factory()->create();
        $data = [
            'name' => 'Test Site',
            'address' => '123 Test St',
            'latitude' => 0,
            'longitude' => -181, // Invalid
            'timezone' => 'UTC',
        ];

        $response = $this->actingAs($user)->postJson('/api/sites', $data);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['longitude']);
    }

    public function test_show_returns_site_for_owner(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['owner_id' => $user->id, 'owner_type' => User::class]);

        $response = $this->actingAs($user)->getJson("/api/sites/{$site->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Site retrieved successfully',
            ])
            ->assertJsonPath('data.id', $site->id);
    }

    public function test_show_returns_forbidden_for_non_owner(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $site = Site::factory()->create(['owner_id' => $owner->id, 'owner_type' => User::class]);

        $response = $this->actingAs($otherUser)->getJson("/api/sites/{$site->id}");

        $response->assertForbidden();
    }

    public function test_show_returns_not_found_for_nonexistent_site(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/sites/999');

        $response->assertNotFound();
    }

    public function test_endpoints_require_authentication(): void
    {
        $this->getJson('/api/sites')->assertUnauthorized();
        $this->postJson('/api/sites')->assertUnauthorized();
        $this->getJson('/api/sites/1')->assertUnauthorized();
    }
}
