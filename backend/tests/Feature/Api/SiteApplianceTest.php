<?php

namespace Tests\Feature\Api;

use App\Models\Appliance;
use App\Models\Category;
use App\Models\Site;
use App\Models\SiteAppliance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SiteApplianceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Site $site;

    private Appliance $appliance;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->site = Site::factory()->create(['owner_id' => $this->user->id, 'owner_type' => User::class]);
        $category = Category::factory()->create(['user_id' => $this->user->id]);
        $this->appliance = Appliance::factory()->create(['category_id' => $category->id, 'owner_id' => $this->user->id, 'owner_type' => User::class]);

        Sanctum::actingAs($this->user);
    }

    public function test_add_appliance_to_site_success(): void
    {
        $response = $this->postJson("/api/sites/{$this->site->id}/appliances", [
            'appliance_id' => $this->appliance->id,
            'quantity' => 2,
            'daily_usage_hours' => 8.5,
            'notes' => 'Test appliance notes',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Appliance added to site successfully',
                'data' => [
                    'added_by_id' => $this->user->id,
                    'added_by_type' => User::class,
                    'site_id' => $this->site->id,
                    'appliance_id' => $this->appliance->id,
                    'quantity' => 2,
                    'daily_usage_hours' => '8.50',
                    'notes' => 'Test appliance notes',
                ],
            ]);

        $this->assertDatabaseHas('site_appliances', [
            'added_by_id' => $this->user->id,
            'added_by_type' => User::class,
            'site_id' => $this->site->id,
            'appliance_id' => $this->appliance->id,
            'quantity' => 2,
            'daily_usage_hours' => 8.5,
            'notes' => 'Test appliance notes',
        ]);
    }

    public function test_add_appliance_validation_errors(): void
    {
        $response = $this->postJson("/api/sites/{$this->site->id}/appliances", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['appliance_id', 'quantity', 'daily_usage_hours']);
    }

    public function test_add_appliance_quantity_validation(): void
    {
        $response = $this->postJson("/api/sites/{$this->site->id}/appliances", [
            'appliance_id' => $this->appliance->id,
            'quantity' => 0,
            'daily_usage_hours' => 8.0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    public function test_add_appliance_daily_usage_hours_validation(): void
    {
        $response = $this->postJson("/api/sites/{$this->site->id}/appliances", [
            'appliance_id' => $this->appliance->id,
            'quantity' => 1,
            'daily_usage_hours' => 25,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['daily_usage_hours']);

        $response = $this->postJson("/api/sites/{$this->site->id}/appliances", [
            'appliance_id' => $this->appliance->id,
            'quantity' => 1,
            'daily_usage_hours' => -1,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['daily_usage_hours']);
    }

    public function test_add_appliance_edge_case_24_hours(): void
    {
        $response = $this->postJson("/api/sites/{$this->site->id}/appliances", [
            'appliance_id' => $this->appliance->id,
            'quantity' => 1,
            'daily_usage_hours' => 24,
        ]);

        $response->assertStatus(201);
    }

    public function test_add_appliance_edge_case_0_hours(): void
    {
        $response = $this->postJson("/api/sites/{$this->site->id}/appliances", [
            'appliance_id' => $this->appliance->id,
            'quantity' => 1,
            'daily_usage_hours' => 0,
        ]);

        $response->assertStatus(201);
    }

    public function test_add_appliance_edge_case_24_5_hours_fails(): void
    {
        $response = $this->postJson("/api/sites/{$this->site->id}/appliances", [
            'appliance_id' => $this->appliance->id,
            'quantity' => 1,
            'daily_usage_hours' => 24.5,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['daily_usage_hours']);
    }

    public function test_add_appliance_non_existent_appliance(): void
    {
        $response = $this->postJson("/api/sites/{$this->site->id}/appliances", [
            'appliance_id' => 999,
            'quantity' => 1,
            'daily_usage_hours' => 8.0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['appliance_id']);
    }

    public function test_add_appliance_duplicate_returns_409(): void
    {
        // Add appliance first time
        SiteAppliance::create([
            'added_by_id' => $this->user->id,
            'added_by_type' => User::class,
            'site_id' => $this->site->id,
            'appliance_id' => $this->appliance->id,
            'quantity' => 1,
            'daily_usage_hours' => 5.0,
        ]);

        // Try to add same appliance again
        $response = $this->postJson("/api/sites/{$this->site->id}/appliances", [
            'appliance_id' => $this->appliance->id,
            'quantity' => 2,
            'daily_usage_hours' => 8.0,
        ]);

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'error' => 'Appliance already added to this site',
            ]);
    }

    public function test_add_appliance_to_non_owned_site_returns_404(): void
    {
        $otherUser = User::factory()->create();
        $otherSite = Site::factory()->create(['owner_id' => $otherUser->id, 'owner_type' => User::class]);

        $response = $this->postJson("/api/sites/{$otherSite->id}/appliances", [
            'appliance_id' => $this->appliance->id,
            'quantity' => 1,
            'daily_usage_hours' => 8.0,
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'error' => 'Site not found or access denied',
            ]);
    }

    public function test_add_appliance_to_non_existent_site_returns_404(): void
    {
        $response = $this->postJson('/api/sites/999/appliances', [
            'appliance_id' => $this->appliance->id,
            'quantity' => 1,
            'daily_usage_hours' => 8.0,
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'error' => 'Site not found or access denied',
            ]);
    }

    public function test_add_appliance_without_notes(): void
    {
        $response = $this->postJson("/api/sites/{$this->site->id}/appliances", [
            'appliance_id' => $this->appliance->id,
            'quantity' => 1,
            'daily_usage_hours' => 8.0,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'notes' => null,
                ],
            ]);
    }
}
