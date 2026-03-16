<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Organisation;
use App\Models\OrganisationMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganisationManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
    }

    public function test_admin_can_list_organisations(): void
    {
        Organisation::factory()->count(5)->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/organisations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'name', 'type', 'status'],
                ],
            ])
            ->assertJson(['success' => true]);
    }

    public function test_admin_can_filter_organisations_by_type(): void
    {
        Organisation::factory()->count(3)->create(['type' => 'installer']);
        Organisation::factory()->count(2)->create(['type' => 'provider']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/organisations?type=installer');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(3, $data);
        foreach ($data as $org) {
            $this->assertEquals('installer', $org['type']);
        }
    }

    public function test_admin_can_filter_organisations_by_status(): void
    {
        Organisation::factory()->count(2)->create(['status' => 'suspended']);
        Organisation::factory()->count(3)->create(['status' => 'active']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/organisations?status=suspended');

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_admin_can_search_organisations(): void
    {
        Organisation::factory()->create(['name' => 'UniqueOrg Inc', 'email' => 'uniqueorg@test.com']);
        Organisation::factory()->count(5)->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/organisations?search=uniqueorg@test.com');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_admin_can_update_organisation_status(): void
    {
        $org = Organisation::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/admin/organisations/{$org->id}/status", [
                'status' => 'suspended',
                'reason' => 'Compliance violation',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.status', 'suspended');

        $this->assertDatabaseHas('organisations', ['id' => $org->id, 'status' => 'suspended']);
    }

    public function test_admin_can_view_organisation_audit_logs(): void
    {
        $org = Organisation::factory()->create(['status' => 'active']);

        // Create audit log via status change
        $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/admin/organisations/{$org->id}/status", ['status' => 'suspended']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/admin/organisations/{$org->id}/audit-logs");

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'action', 'old_values', 'new_values', 'created_at'],
                ],
            ]);
    }

    public function test_admin_can_view_member_audit_logs(): void
    {
        $org = Organisation::factory()->create(['status' => 'active']);
        $user = User::factory()->create(['status' => 'active']);
        $member = OrganisationMember::factory()->create([
            'organisation_id' => $org->id,
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        // Create audit log via status change using the org member endpoint
        $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/organisations/{$org->id}/members/{$member->id}/status", ['status' => 'suspended']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/organisations/{$org->id}/members/{$member->id}/audit-logs");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_non_admin_cannot_access_organisation_management(): void
    {
        $regularUser = User::factory()->create(['role' => 'customer', 'status' => 'active']);

        $response = $this->actingAs($regularUser, 'sanctum')
            ->getJson('/api/admin/organisations');

        $response->assertStatus(403);
    }

    public function test_organisation_status_change_creates_audit_log(): void
    {
        $org = Organisation::factory()->create(['status' => 'active']);

        $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/admin/organisations/{$org->id}/status", [
                'status' => 'suspended',
                'reason' => 'Org audit test',
            ]);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Organisation::class,
            'auditable_id' => $org->id,
            'action' => AuditLog::ACTION_STATUS_CHANGED,
            'reason' => 'Org audit test',
        ]);
    }

    public function test_validation_fails_for_invalid_organisation_status(): void
    {
        $org = Organisation::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/admin/organisations/{$org->id}/status", [
                'status' => 'closed', // invalid
            ]);

        $response->assertStatus(422);
    }

    public function test_non_admin_cannot_view_member_audit_logs(): void
    {
        $org = Organisation::factory()->create(['status' => 'active']);
        $regularUser = User::factory()->create(['role' => 'customer', 'status' => 'active']);
        $member = OrganisationMember::factory()->create([
            'organisation_id' => $org->id,
            'user_id' => $regularUser->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($regularUser, 'sanctum')
            ->getJson("/api/organisations/{$org->id}/members/{$member->id}/audit-logs");

        $response->assertStatus(403);
    }
}
