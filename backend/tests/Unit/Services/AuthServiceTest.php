<?php

namespace Tests\Unit\Services;

use App\Actions\Auth\GenerateAuthTokenAction;
use App\Actions\Auth\GetAuthenticatedUserAction;
use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\RegisterInstallerAction;
use App\Actions\Auth\RegisterProviderAction;
use App\Actions\Auth\RegisterUserAction;
use App\Actions\Auth\RevokeAuthTokenAction;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $service;

    private RegisterUserAction $registerUserAction;

    private RegisterInstallerAction $registerInstallerAction;

    private RegisterProviderAction $registerProviderAction;

    private LoginUserAction $loginUserAction;

    private GenerateAuthTokenAction $generateAuthTokenAction;

    private RevokeAuthTokenAction $revokeAuthTokenAction;

    private GetAuthenticatedUserAction $getAuthenticatedUserAction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerUserAction = app(RegisterUserAction::class);
        $this->registerInstallerAction = app(RegisterInstallerAction::class);
        $this->registerProviderAction = app(RegisterProviderAction::class);
        $this->loginUserAction = app(LoginUserAction::class);
        $this->generateAuthTokenAction = app(GenerateAuthTokenAction::class);
        $this->revokeAuthTokenAction = app(RevokeAuthTokenAction::class);
        $this->getAuthenticatedUserAction = app(GetAuthenticatedUserAction::class);

        $this->service = new AuthService(
            $this->registerUserAction,
            $this->registerInstallerAction,
            $this->registerProviderAction,
            $this->loginUserAction,
            $this->generateAuthTokenAction,
            $this->revokeAuthTokenAction,
            $this->getAuthenticatedUserAction
        );
    }

    /** @test */
    public function register_creates_user_and_returns_token(): void
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $result = $this->service->register($data);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('access_token', $result);
        $this->assertInstanceOf(User::class, $result['user']);
        $this->assertIsString($result['access_token']);
        $this->assertEquals('john@example.com', $result['user']->email);
    }

    /** @test */
    public function register_uses_database_transaction(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        $this->service->register($data);
    }

    /** @test */
    public function register_creates_user_with_all_fields(): void
    {
        $data = [
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'other_names' => 'Marie',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'phone' => '+233500000000',
            'address' => 'Accra, Ghana',
            'role' => 'provider',
        ];

        $result = $this->service->register($data);

        $this->assertEquals('Marie', $result['user']->other_names);
        $this->assertEquals('+233500000000', $result['user']->phone);
        $this->assertEquals('Accra, Ghana', $result['user']->address);
        $this->assertEquals('provider', $result['user']->role);
    }

    /** @test */
    public function login_returns_user_and_token_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $result = $this->service->login([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('access_token', $result);
        $this->assertEquals($user->id, $result['user']->id);
        $this->assertIsString($result['access_token']);
    }

    /** @test */
    public function login_returns_null_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $result = $this->service->login([
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertNull($result);
    }

    /** @test */
    public function login_returns_null_when_user_not_found(): void
    {
        $result = $this->service->login([
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $this->assertNull($result);
    }

    /** @test */
    public function logout_revokes_user_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token');
        $user->withAccessToken($token->accessToken);

        $this->service->logout($user);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }

    /** @test */
    public function get_authenticated_user_returns_user(): void
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ]);

        $result = $this->service->getAuthenticatedUser($user);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id, $result->id);
        $this->assertEquals('john@example.com', $result->email);
        $this->assertEquals('John', $result->first_name);
    }
}
