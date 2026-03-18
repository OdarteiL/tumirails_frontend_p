<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function register_creates_user_and_returns_token(): void
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'role',
                        'status',
                    ],
                    'access_token',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Registration successful',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function register_validates_required_fields(): void
    {
        $response = $this->postJson('/api/auth/register', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'error',
                'errors' => [
                    'first_name',
                    'last_name',
                    'email',
                    'password',
                ],
            ])
            ->assertJson([
                'success' => false,
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function register_validates_email_format(): void
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertStatus(422)
            ->assertJsonPath('errors.email', fn ($errors) => count($errors) > 0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function register_validates_unique_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertStatus(422)
            ->assertJsonPath('errors.email', fn ($errors) => count($errors) > 0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function register_validates_password_confirmation(): void
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different',
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertStatus(422)
            ->assertJsonPath('errors.password', fn ($errors) => count($errors) > 0);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function register_accepts_optional_fields(): void
    {
        $data = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'other_names' => 'Marie',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '+233500000000',
            'address' => 'Accra, Ghana',
            'role' => 'provider',
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'other_names' => 'Marie',
            'phone' => '+233500000000',
            'address' => 'Accra, Ghana',
            'role' => 'provider',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function login_authenticates_user_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                    ],
                    'access_token',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Login successful',
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function login_fails_with_invalid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'error' => 'Invalid credentials',
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function login_validates_required_fields(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'error',
                'errors' => [
                    'email',
                    'password',
                ],
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function logout_revokes_current_token(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Successfully logged out',
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function logout_requires_authentication(): void
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(401);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function me_returns_authenticated_user(): void
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'role',
                        'status',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'User retrieved successfully',
                'data' => [
                    'user' => [
                        'email' => 'john@example.com',
                        'first_name' => 'John',
                        'last_name' => 'Doe',
                    ],
                ],
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function me_requires_authentication(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function register_installer_creates_user_and_installer_and_returns_token(): void
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Installer',
            'email' => 'john@installer.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_name' => 'ABC Installations',
            'license_number' => 'LIC-12345',
            'service_areas' => ['Accra', 'Kumasi'],
            'certifications' => ['Solar PV', 'Electrical'],
            'years_experience' => 5,
        ];

        $response = $this->postJson('/api/auth/register/installer', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'first_name', 'last_name', 'email', 'role', 'status'],
                    'installer' => ['id', 'user_id', 'company_name', 'license_number', 'service_areas', 'certifications', 'years_experience', 'rating'],
                    'access_token',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Installer registration successful',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@installer.com',
            'role' => 'installer',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('installer_details', [
            'company_name' => 'ABC Installations',
            'license_number' => 'LIC-12345',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function register_installer_validates_required_fields(): void
    {
        $response = $this->postJson('/api/auth/register/installer', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'first_name',
                'last_name',
                'email',
                'password',
                'license_number',
                'service_areas',
                'years_experience',
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function register_installer_validates_unique_license_number(): void
    {
        $existingUser = User::factory()->create(['role' => 'installer']);
        $existingUser->installerDetail()->create([
            'company_name' => 'Existing Company',
            'license_number' => 'LIC-EXIST',
            'service_areas' => ['Area'],
            'years_experience' => 3,
        ]);

        $data = [
            'first_name' => 'New',
            'last_name' => 'Installer',
            'email' => 'new@installer.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_name' => 'New Company',
            'license_number' => 'LIC-EXIST',
            'service_areas' => ['Accra'],
            'years_experience' => 2,
        ];

        $response = $this->postJson('/api/auth/register/installer', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['license_number']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function register_installer_validates_service_areas_array(): void
    {
        $data = [
            'first_name' => 'Test',
            'last_name' => 'Installer',
            'email' => 'test@installer.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_name' => 'Test Company',
            'license_number' => 'LIC-TEST',
            'service_areas' => 'Not an array',
            'years_experience' => 1,
        ];

        $response = $this->postJson('/api/auth/register/installer', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['service_areas']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function register_provider_creates_user_and_provider_and_returns_token(): void
    {
        $data = [
            'first_name' => 'Sarah',
            'last_name' => 'Provider',
            'email' => 'sarah@provider.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_name' => 'Solar Providers Ltd',
            'business_registration' => 'BRN-12345',
            'service_areas' => ['Greater Accra', 'Ashanti'],
            'certifications' => ['ISO 9001', 'Solar Alliance'],
        ];

        $response = $this->postJson('/api/auth/register/provider', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => ['id', 'first_name', 'last_name', 'email', 'role', 'status'],
                    'provider' => ['id', 'user_id', 'company_name', 'business_registration', 'service_areas', 'certifications', 'rating'],
                    'access_token',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Provider registration successful',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'sarah@provider.com',
            'role' => 'provider',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('provider_details', [
            'company_name' => 'Solar Providers Ltd',
            'business_registration' => 'BRN-12345',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function register_provider_validates_required_fields(): void
    {
        $response = $this->postJson('/api/auth/register/provider', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'first_name',
                'last_name',
                'email',
                'password',
                'business_registration',
                'service_areas',
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function register_provider_validates_unique_business_registration(): void
    {
        $existingUser = User::factory()->create(['role' => 'provider']);
        $existingUser->providerDetail()->create([
            'company_name' => 'Existing Provider',
            'business_registration' => 'BRN-EXIST',
            'service_areas' => ['Area'],
        ]);

        $data = [
            'first_name' => 'New',
            'last_name' => 'Provider',
            'email' => 'new@provider.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_name' => 'New Provider',
            'business_registration' => 'BRN-EXIST',
            'service_areas' => ['Accra'],
        ];

        $response = $this->postJson('/api/auth/register/provider', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['business_registration']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function register_provider_validates_service_areas_array(): void
    {
        $data = [
            'first_name' => 'Test',
            'last_name' => 'Provider',
            'email' => 'test@provider.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_name' => 'Test Provider',
            'business_registration' => 'BRN-TEST',
            'service_areas' => 'Not an array',
        ];

        $response = $this->postJson('/api/auth/register/provider', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['service_areas']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function installer_can_authenticate_after_registration(): void
    {
        $data = [
            'first_name' => 'Auth',
            'last_name' => 'Test',
            'email' => 'auth@installer.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_name' => 'Auth Company',
            'license_number' => 'LIC-AUTH',
            'service_areas' => ['Accra'],
            'years_experience' => 1,
        ];

        $registerResponse = $this->postJson('/api/auth/register/installer', $data);
        $registerResponse->assertStatus(201);

        $token = $registerResponse->json('data.access_token');

        $meResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me');

        $meResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'email' => 'auth@installer.com',
                        'role' => 'installer',
                    ],
                ],
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function provider_can_authenticate_after_registration(): void
    {
        $data = [
            'first_name' => 'Auth',
            'last_name' => 'Test',
            'email' => 'auth@provider.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'company_name' => 'Auth Provider',
            'business_registration' => 'BRN-AUTH',
            'service_areas' => ['Accra'],
        ];

        $registerResponse = $this->postJson('/api/auth/register/provider', $data);
        $registerResponse->assertStatus(201);

        $token = $registerResponse->json('data.access_token');

        $meResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/auth/me');

        $meResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'email' => 'auth@provider.com',
                        'role' => 'provider',
                    ],
                ],
            ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function forgot_password_returns_success_for_existing_email(): void
    {
        Mail::fake();
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson('/api/auth/forgot-password', ['email' => 'test@example.com']);

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function forgot_password_returns_success_for_nonexistent_email(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/auth/forgot-password', ['email' => 'nobody@example.com']);

        // Always 200 to prevent email enumeration
        $response->assertStatus(200)->assertJson(['success' => true]);
        Mail::assertNothingSent();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function forgot_password_validates_email_format(): void
    {
        $response = $this->postJson('/api/auth/forgot-password', ['email' => 'not-an-email']);

        $response->assertStatus(422)->assertJsonValidationErrors(['email']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function forgot_password_sends_email_for_existing_user(): void
    {
        Mail::fake();
        User::factory()->create(['email' => 'test@example.com']);

        $this->postJson('/api/auth/forgot-password', ['email' => 'test@example.com']);

        Mail::assertSentCount(1);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function reset_password_succeeds_with_valid_token(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'test@example.com']);
        $plainToken = 'valid-reset-token';
        DB::table('password_reset_tokens')->insert([
            'email' => 'test@example.com',
            'token' => Hash::make($plainToken),
            'created_at' => now(),
        ]);

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => 'test@example.com',
            'token' => $plainToken,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function reset_password_fails_with_invalid_token(): void
    {
        Mail::fake();
        User::factory()->create(['email' => 'test@example.com']);
        DB::table('password_reset_tokens')->insert([
            'email' => 'test@example.com',
            'token' => Hash::make('correct-token'),
            'created_at' => now(),
        ]);

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => 'test@example.com',
            'token' => 'wrong-token',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(422)->assertJson(['success' => false]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function reset_password_fails_with_expired_token(): void
    {
        Mail::fake();
        User::factory()->create(['email' => 'test@example.com']);
        $plainToken = 'expired-token';
        DB::table('password_reset_tokens')->insert([
            'email' => 'test@example.com',
            'token' => Hash::make($plainToken),
            'created_at' => now()->subMinutes(61),
        ]);

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => 'test@example.com',
            'token' => $plainToken,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(422)->assertJson(['success' => false]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function reset_password_validates_required_fields(): void
    {
        $response = $this->postJson('/api/auth/reset-password', []);

        $response->assertStatus(422)->assertJsonValidationErrors(['email', 'token', 'password']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function change_password_succeeds_with_correct_current_password(): void
    {
        Mail::fake();
        $user = User::factory()->create(['password' => Hash::make('oldpassword')]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/auth/change-password', [
                'current_password' => 'oldpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function change_password_fails_with_wrong_current_password(): void
    {
        Mail::fake();
        $user = User::factory()->create(['password' => Hash::make('oldpassword')]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/auth/change-password', [
                'current_password' => 'wrongpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertStatus(422)->assertJson(['success' => false]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function change_password_requires_authentication(): void
    {
        $response = $this->postJson('/api/auth/change-password', [
            'current_password' => 'oldpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(401);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function change_password_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/auth/change-password', []);

        $response->assertStatus(422)->assertJsonValidationErrors(['current_password', 'password']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function change_password_sends_confirmation_email(): void
    {
        Mail::fake();
        $user = User::factory()->create(['password' => Hash::make('oldpassword')]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/auth/change-password', [
                'current_password' => 'oldpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        Mail::assertSentCount(1);
    }
}
