<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_creation_endpoint_smoke_test(): void
    {
        $user = User::factory()->create();
        $data = [
            'name' => 'CI Test Site',
            'address' => 'Test Address, Lagos, Nigeria',
            'latitude' => 6.5244,
            'longitude' => 3.3792,
            'timezone' => 'Africa/Lagos',
            'notes' => 'CI smoke test site',
        ];

        $response = $this->actingAs($user)->postJson('/api/sites', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'user_id',
                    'name',
                    'address',
                    'latitude',
                    'longitude',
                    'timezone',
                    'notes',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Site created successfully',
            ]);
    }

    public function test_site_creation_coordinate_validation(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/sites', [
            'name' => 'Invalid Site',
            'address' => 'Test Address',
            'latitude' => 91, // Invalid
            'longitude' => -181, // Invalid
            'timezone' => 'UTC',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['latitude', 'longitude']);
    }

    public function test_site_creation_requires_authentication(): void
    {
        $response = $this->postJson('/api/sites', [
            'name' => 'Test Site',
            'address' => 'Test Address',
            'latitude' => 0,
            'longitude' => 0,
            'timezone' => 'UTC',
        ]);

        $response->assertStatus(401);
    }
}
