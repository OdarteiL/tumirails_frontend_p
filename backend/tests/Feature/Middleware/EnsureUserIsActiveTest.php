<?php

namespace Tests\Feature\Middleware;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnsureUserIsActiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_access_protected_routes(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/sites');

        // May be empty, but should not be blocked
        $response->assertStatus(200);
    }

    public function test_suspended_user_cannot_access_protected_routes(): void
    {
        $suspended = User::factory()->create(['status' => 'suspended']);

        $endpoints = [
            '/api/sites',
            '/api/estimations',
            '/api/appliances',
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->actingAs($suspended, 'sanctum')->getJson($endpoint);
            $response->assertStatus(403)
                ->assertJsonPath('code', 'ACCOUNT_SUSPENDED');
        }
    }

    public function test_middleware_allows_unauthenticated_routes(): void
    {
        // Login endpoint should not be blocked
        $response = $this->postJson('/api/auth/login', [
            'email' => 'notexist@example.com',
            'password' => 'password',
        ]);

        // Not 403 - could be 401 or 422 but not a middleware suspension block
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_middleware_applies_to_all_auth_required_routes(): void
    {
        $suspended = User::factory()->create(['status' => 'suspended']);

        $endpoints = [
            ['GET', '/api/sites'],
            ['GET', '/api/estimations'],
        ];

        foreach ($endpoints as [$method, $endpoint]) {
            $response = $this->actingAs($suspended, 'sanctum')
                ->json($method, $endpoint);
            $response->assertStatus(403);
        }
    }

    public function test_error_response_format(): void
    {
        $suspended = User::factory()->create(['status' => 'suspended']);

        $response = $this->actingAs($suspended, 'sanctum')
            ->getJson('/api/sites');

        $response->assertStatus(403)
            ->assertJsonStructure(['success', 'error', 'code'])
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'ACCOUNT_SUSPENDED');

        $this->assertStringContainsStringIgnoringCase('suspended', $response->json('error'));
    }

    public function test_active_user_after_reactivation_can_access_routes(): void
    {
        $user = User::factory()->create(['status' => 'suspended']);

        // Initially blocked
        $this->actingAs($user, 'sanctum')->getJson('/api/sites')->assertStatus(403);

        // After reactivation
        $user->update(['status' => 'active']);

        $this->actingAs($user, 'sanctum')->getJson('/api/sites')->assertStatus(200);
    }
}
