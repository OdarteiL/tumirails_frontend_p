<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
    }

    public function test_admin_can_list_users(): void
    {
        User::factory()->count(5)->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => ['id', 'email', 'role', 'status'],
                ],
                'meta' => ['current_page', 'total'],
            ])
            ->assertJson(['success' => true]);
    }

    public function test_admin_can_filter_users_by_role(): void
    {
        User::factory()->count(3)->create(['role' => 'installer']);
        User::factory()->count(2)->create(['role' => 'customer']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/users?role=installer');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(3, $data);
        foreach ($data as $user) {
            $this->assertEquals('installer', $user['role']);
        }
    }

    public function test_admin_can_filter_users_by_status(): void
    {
        User::factory()->count(2)->create(['status' => 'suspended']);
        User::factory()->count(3)->create(['status' => 'active']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/users?status=suspended');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(2, $data);
    }

    public function test_admin_can_search_users(): void
    {
        User::factory()->create(['first_name' => 'Unique', 'last_name' => 'Testuser', 'email' => 'unique@test.com']);
        User::factory()->count(5)->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/admin/users?search=unique@test.com');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    public function test_admin_can_update_user_status(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/admin/users/{$user->id}/status", [
                'status' => 'suspended',
                'reason' => 'Test suspension',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.status', 'suspended');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'status' => 'suspended']);
    }

    public function test_admin_can_view_user_audit_logs(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        // Create some audit logs via status change
        $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/admin/users/{$user->id}/status", ['status' => 'suspended']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/admin/users/{$user->id}/audit-logs");

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'action', 'old_values', 'new_values', 'created_at'],
                ],
            ]);
    }

    public function test_non_admin_cannot_access_user_management(): void
    {
        $regularUser = User::factory()->create(['role' => 'customer', 'status' => 'active']);

        $response = $this->actingAs($regularUser, 'sanctum')
            ->getJson('/api/admin/users');

        $response->assertStatus(403);
    }

    public function test_suspended_user_cannot_access_protected_routes(): void
    {
        $suspended = User::factory()->create(['status' => 'suspended']);

        $response = $this->actingAs($suspended, 'sanctum')
            ->getJson('/api/sites');

        $response->assertStatus(403)
            ->assertJsonPath('code', 'ACCOUNT_SUSPENDED');
    }

    public function test_status_change_creates_audit_log(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/admin/users/{$user->id}/status", [
                'status' => 'suspended',
                'reason' => 'Audit test',
            ]);

        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'action' => AuditLog::ACTION_STATUS_CHANGED,
            'reason' => 'Audit test',
        ]);

        $log = AuditLog::where('auditable_id', $user->id)->first();
        $this->assertEquals(['status' => 'active'], $log->old_values);
        $this->assertEquals(['status' => 'suspended'], $log->new_values);
    }

    public function test_admin_cannot_suspend_themselves(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/admin/users/{$this->admin->id}/status", [
                'status' => 'suspended',
            ]);

        $response->assertStatus(422);
        $this->assertDatabaseHas('users', ['id' => $this->admin->id, 'status' => 'active']);
    }

    public function test_validation_fails_for_invalid_status(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->patchJson("/api/admin/users/{$user->id}/status", [
                'status' => 'banned', // invalid
            ]);

        $response->assertStatus(422);
    }
}
