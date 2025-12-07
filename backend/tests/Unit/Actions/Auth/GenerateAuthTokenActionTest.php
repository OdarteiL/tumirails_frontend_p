<?php

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\GenerateAuthTokenAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateAuthTokenActionTest extends TestCase
{
    use RefreshDatabase;

    private GenerateAuthTokenAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new GenerateAuthTokenAction();
    }

    /** @test */
    public function it_generates_token_for_user(): void
    {
        $user = User::factory()->create();

        $token = $this->action->execute($user);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
    }

    /** @test */
    public function it_creates_personal_access_token_record(): void
    {
        $user = User::factory()->create();

        $this->action->execute($user);

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
            'name' => 'auth_token',
        ]);
    }

    /** @test */
    public function it_uses_custom_token_name_when_provided(): void
    {
        $user = User::factory()->create();

        $this->action->execute($user, 'custom_token');

        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'custom_token',
        ]);
    }

    /** @test */
    public function it_generates_different_tokens_for_same_user(): void
    {
        $user = User::factory()->create();

        $token1 = $this->action->execute($user);
        $token2 = $this->action->execute($user);

        $this->assertNotEquals($token1, $token2);
    }
}
