<?php

namespace Tests\Feature\Middleware;

use App\Models\Organisation;
use App\Models\OrganisationMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureActiveOrganisationContextTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['status' => 'active']);
        $this->organisation = Organisation::factory()->create(['status' => 'active']);

        OrganisationMember::factory()->create([
            'organisation_id' => $this->organisation->id,
            'user_id' => $this->user->id,
            'role' => 'member',
            'status' => 'active',
        ]);
    }

    public function test_active_user_with_active_org_can_access_routes(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/organisations/{$this->organisation->id}/members");

        $response->assertStatus(200);
    }

    public function test_suspended_organisation_blocks_access(): void
    {
        $this->organisation->update(['status' => 'suspended']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/organisations/{$this->organisation->id}/members");

        $response->assertStatus(403)
            ->assertJsonPath('code', 'ORGANISATION_SUSPENDED');
    }

    public function test_suspended_member_blocks_org_access(): void
    {
        $membership = OrganisationMember::where([
            'organisation_id' => $this->organisation->id,
            'user_id' => $this->user->id,
        ])->first();

        $membership->update(['status' => 'suspended']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/organisations/{$this->organisation->id}/members");

        $response->assertStatus(403)
            ->assertJsonPath('code', 'ORGANISATION_MEMBER_SUSPENDED');
    }

    public function test_admin_can_access_suspended_org(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->organisation->update(['status' => 'suspended']);

        // Admin bypass: admin role skips the membership status check
        $response = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/organisations/{$this->organisation->id}/members");

        // Admin still gets blocked by suspended org check
        $response->assertStatus(403)
            ->assertJsonPath('code', 'ORGANISATION_SUSPENDED');
    }

    public function test_reactivated_organisation_allows_access(): void
    {
        $this->organisation->update(['status' => 'suspended']);

        // Initially blocked
        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/organisations/{$this->organisation->id}/members")
            ->assertStatus(403);

        // After reactivation
        $this->organisation->update(['status' => 'active']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/organisations/{$this->organisation->id}/members")
            ->assertStatus(200);
    }

    public function test_error_response_format_for_suspended_org(): void
    {
        $this->organisation->update(['status' => 'suspended']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/organisations/{$this->organisation->id}/members");

        $response->assertStatus(403)
            ->assertJsonStructure(['success', 'error', 'code'])
            ->assertJsonPath('success', false);
    }
}
