<?php

namespace Tests\Unit\Actions\Email;

use App\Actions\Email\SendPasswordResetEmailAction;
use App\Mail\PasswordResetMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendPasswordResetEmailActionTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_sends_password_reset_email(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'test@example.com']);
        $action = new SendPasswordResetEmailAction();

        $action->execute($user, 'some-token');

        Mail::assertSent(PasswordResetMail::class, function (PasswordResetMail $mail) use ($user) {
            return $mail->hasTo($user->email)
                && $mail->user->id === $user->id
                && str_contains($mail->resetUrl, 'some-token')
                && str_contains($mail->resetUrl, urlencode($user->email));
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_sends_exactly_one_email(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $action = new SendPasswordResetEmailAction();

        $action->execute($user, 'token');

        Mail::assertSentCount(1);
    }
}
