<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Organisation;
use App\Models\OrganisationMember;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganisationSiteControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_organisation_index_returns_organisation_sites(): void
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create(['type' => 'customer']);

        // Add user as member
        OrganisationMember::create([
            'organisation_id' => $organisation->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'joined_at' => now(),
        ]);

        // Create sites for organisation
        Site::factory()->count(3)->create([
            'owner_id' => $organisation->id,
            'owner_type' => Organisation::class,
        ]);

        // Create sites for other organisation
        Site::factory()->count(2)->create();

        $response = $this->actingAs($user)->getJson("/api/organisations/{$organisation->id}/sites");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Organisation sites retrieved successfully',
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_organisation_index_requires_membership(): void
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create(['type' => 'customer']);

        $response = $this->actingAs($user)->getJson("/api/organisations/{$organisation->id}/sites");

        $response->assertForbidden()
            ->assertJson([
                'success' => false,
                'error' => 'You do not have access to this organisation',
            ]);
    }

    public function test_organisation_store_creates_site_successfully(): void
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create(['type' => 'customer']);

        OrganisationMember::create([
            'organisation_id' => $organisation->id,
            'user_id' => $user->id,
            'role' => 'admin',
            'joined_at' => now(),
        ]);

        $data = [
            'name' => 'Org Site',
            'address' => '456 Org St',
            'latitude' => 34.0522,
            'longitude' => -118.2437,
            'timezone' => 'America/Los_Angeles',
            'notes' => 'Organisation site',
        ];

        $response = $this->actingAs($user)->postJson("/api/organisations/{$organisation->id}/sites", $data);

        $response->assertCreated()
            ->assertJson([
                'success' => true,
                'message' => 'Site created successfully',
            ])
            ->assertJsonPath('data.name', 'Org Site');

        $this->assertDatabaseHas('sites', [
            'name' => 'Org Site',
            'owner_id' => $organisation->id,
            'owner_type' => Organisation::class,
        ]);
    }

    public function test_organisation_store_requires_admin_permission(): void
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create(['type' => 'customer']);

        // Add user as regular member (not admin)
        OrganisationMember::create([
            'organisation_id' => $organisation->id,
            'user_id' => $user->id,
            'role' => 'member',
            'joined_at' => now(),
        ]);

        $data = [
            'name' => 'Org Site',
            'address' => '456 Org St',
            'latitude' => 34.0522,
            'longitude' => -118.2437,
            'timezone' => 'America/Los_Angeles',
        ];

        $response = $this->actingAs($user)->postJson("/api/organisations/{$organisation->id}/sites", $data);

        $response->assertForbidden()
            ->assertJson([
                'success' => false,
                'error' => 'You do not have permission to create sites for this organisation',
            ]);
    }

    public function test_organisation_show_returns_site_for_member(): void
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create(['type' => 'customer']);

        OrganisationMember::create([
            'organisation_id' => $organisation->id,
            'user_id' => $user->id,
            'role' => 'member',
            'joined_at' => now(),
        ]);

        $site = Site::factory()->create([
            'owner_id' => $organisation->id,
            'owner_type' => Organisation::class,
        ]);

        $response = $this->actingAs($user)->getJson("/api/organisations/{$organisation->id}/sites/{$site->id}");

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Site retrieved successfully',
            ])
            ->assertJsonPath('data.id', $site->id);
    }

    public function test_organisation_show_returns_forbidden_for_non_member(): void
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create(['type' => 'customer']);

        $site = Site::factory()->create([
            'owner_id' => $organisation->id,
            'owner_type' => Organisation::class,
        ]);

        $response = $this->actingAs($user)->getJson("/api/organisations/{$organisation->id}/sites/{$site->id}");

        $response->assertForbidden()
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_organisation_show_returns_not_found_for_wrong_organisation(): void
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create(['type' => 'customer']);
        $otherOrganisation = Organisation::factory()->create(['type' => 'customer']);

        OrganisationMember::create([
            'organisation_id' => $organisation->id,
            'user_id' => $user->id,
            'role' => 'member',
            'joined_at' => now(),
        ]);

        // Site belongs to different organisation
        $site = Site::factory()->create([
            'owner_id' => $otherOrganisation->id,
            'owner_type' => Organisation::class,
        ]);

        $response = $this->actingAs($user)->getJson("/api/organisations/{$organisation->id}/sites/{$site->id}");

        $response->assertForbidden()
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_create_organisation_transfers_sites_when_requested(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        // Create some sites for the user
        Site::factory()->count(2)->create([
            'owner_id' => $user->id,
            'owner_type' => User::class,
        ]);

        $data = [
            'name' => 'Test Customer Org',
            'type' => 'customer',
            'email' => 'org@example.com',
            'transfer_sites' => true,
        ];

        $response = $this->actingAs($user)->postJson('/api/organisations', $data);

        $response->assertCreated();

        $organisationId = $response->json('data.id');

        // Verify sites were transferred
        $this->assertDatabaseHas('sites', [
            'owner_id' => $organisationId,
            'owner_type' => Organisation::class,
        ]);

        $this->assertDatabaseMissing('sites', [
            'owner_id' => $user->id,
            'owner_type' => User::class,
        ]);
    }

    public function test_create_organisation_does_not_transfer_sites_when_not_requested(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        Site::factory()->count(2)->create([
            'owner_id' => $user->id,
            'owner_type' => User::class,
        ]);

        $data = [
            'name' => 'Test Customer Org',
            'type' => 'customer',
            'email' => 'org@example.com',
            'transfer_sites' => false,
        ];

        $response = $this->actingAs($user)->postJson('/api/organisations', $data);

        $response->assertCreated();

        // Verify sites remain with user
        $this->assertDatabaseHas('sites', [
            'owner_id' => $user->id,
            'owner_type' => User::class,
        ]);
    }

    public function test_organisation_endpoints_require_authentication(): void
    {
        $organisation = Organisation::factory()->create(['type' => 'customer']);

        $this->getJson("/api/organisations/{$organisation->id}/sites")->assertUnauthorized();
        $this->postJson("/api/organisations/{$organisation->id}/sites")->assertUnauthorized();
        $this->getJson("/api/organisations/{$organisation->id}/sites/1")->assertUnauthorized();
    }
}
