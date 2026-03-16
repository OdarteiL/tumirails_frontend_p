<?php

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\ForgotPasswordAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForgotPasswordActionTest extends TestCase
{
    use RefreshDatabase;

    private ForgotPasswordAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new ForgotPasswordAction();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_user_and_token_for_existing_email(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $result = $this->action->execute('test@example.com');

        $this->assertNotNull($result);
        $this->assertInstanceOf(User::class, $result['user']);
        $this->assertEquals($user->id, $result['user']->id);
        $this->assertIsString($result['token']);
        $this->assertNotEmpty($result['token']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_null_for_nonexistent_email(): void
    {
        $result = $this->action->execute('nobody@example.com');

        $this->assertNull($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_stores_hashed_token_in_database(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $result = $this->action->execute('test@example.com');

        $record = DB::table('password_reset_tokens')->where('email', 'test@example.com')->first();

        $this->assertNotNull($record);
        $this->assertTrue(Hash::check($result['token'], $record->token));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_replaces_existing_token_on_repeat_request(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->action->execute('test@example.com');
        $this->action->execute('test@example.com');

        $count = DB::table('password_reset_tokens')->where('email', 'test@example.com')->count();

        $this->assertEquals(1, $count);
    }
}
