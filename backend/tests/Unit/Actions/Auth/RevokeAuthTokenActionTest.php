<?php

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\RevokeAuthTokenAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevokeAuthTokenActionTest extends TestCase
{
    use RefreshDatabase;

    private RevokeAuthTokenAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new RevokeAuthTokenAction();
    }

    /** @test */
    public function it_revokes_current_access_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token');

        // Simulate authenticated user with current token
        $user->withAccessToken($token->accessToken);

        $result = $this->action->execute($user);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }

    /** @test */
    public function it_returns_false_when_no_current_token(): void
    {
        $user = User::factory()->create();

        $result = $this->action->execute($user);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_only_revokes_current_token_not_all_tokens(): void
    {
        $user = User::factory()->create();
        $token1 = $user->createToken('token1');
        $token2 = $user->createToken('token2');

        // Set token1 as current
        $user->withAccessToken($token1->accessToken);

        $this->action->execute($user);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token1->accessToken->id,
        ]);
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $token2->accessToken->id,
        ]);
    }
}
