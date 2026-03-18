<?php

namespace Tests\Unit\Actions\Auth;

use App\Actions\Auth\ChangePasswordAction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChangePasswordActionTest extends TestCase
{
    use RefreshDatabase;

    private ChangePasswordAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new ChangePasswordAction();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_changes_password_with_correct_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('oldpassword')]);

        $result = $this->action->execute($user, 'oldpassword', 'newpassword123');

        $this->assertTrue($result);
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_false_with_wrong_current_password(): void
    {
        $user = User::factory()->create(['password' => Hash::make('oldpassword')]);

        $result = $this->action->execute($user, 'wrongpassword', 'newpassword123');

        $this->assertFalse($result);
        $this->assertTrue(Hash::check('oldpassword', $user->fresh()->password));
    }
}
