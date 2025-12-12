<?php

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\LoginUserAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginUserActionTest extends TestCase
{
    use RefreshDatabase;

    private LoginUserAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new LoginUserAction();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_user_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $result = $this->action->execute([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id, $result->id);
        $this->assertEquals('test@example.com', $result->email);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_null_with_invalid_email(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $result = $this->action->execute([
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $this->assertNull($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_null_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $result = $this->action->execute([
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertNull($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_null_when_user_does_not_exist(): void
    {
        $result = $this->action->execute([
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $this->assertNull($result);
    }
}
