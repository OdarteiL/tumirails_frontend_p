<?php

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\ResetPasswordAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ResetPasswordActionTest extends TestCase
{
    use RefreshDatabase;

    private ResetPasswordAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new ResetPasswordAction();
    }

    private function seedToken(string $email, string $plainToken, int $minutesAgo = 0): void
    {
        DB::table('password_reset_tokens')->upsert(
            [
                'email' => $email,
                'token' => Hash::make($plainToken),
                'created_at' => now()->subMinutes($minutesAgo),
            ],
            ['email'],
            ['token', 'created_at'],
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_resets_password_with_valid_token(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $this->seedToken('test@example.com', 'valid-token');

        $result = $this->action->execute('test@example.com', 'valid-token', 'newpassword123');

        $this->assertTrue($result);
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_deletes_token_after_successful_reset(): void
    {
        User::factory()->create(['email' => 'test@example.com']);
        $this->seedToken('test@example.com', 'valid-token');

        $this->action->execute('test@example.com', 'valid-token', 'newpassword123');

        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'test@example.com']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_false_for_wrong_token(): void
    {
        User::factory()->create(['email' => 'test@example.com']);
        $this->seedToken('test@example.com', 'correct-token');

        $result = $this->action->execute('test@example.com', 'wrong-token', 'newpassword123');

        $this->assertFalse($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_false_for_nonexistent_email(): void
    {
        $result = $this->action->execute('nobody@example.com', 'any-token', 'newpassword123');

        $this->assertFalse($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_false_and_deletes_expired_token(): void
    {
        User::factory()->create(['email' => 'test@example.com']);
        $this->seedToken('test@example.com', 'valid-token', minutesAgo: 61);

        $result = $this->action->execute('test@example.com', 'valid-token', 'newpassword123');

        $this->assertFalse($result);
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'test@example.com']);
    }
}
