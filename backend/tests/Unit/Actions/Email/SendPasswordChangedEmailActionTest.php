<?php

namespace Tests\Unit\Actions\Email;

use App\Actions\Email\SendPasswordChangedEmailAction;
use App\Mail\PasswordChangedMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendPasswordChangedEmailActionTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_sends_password_changed_email(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'test@example.com']);
        $action = new SendPasswordChangedEmailAction();

        $action->execute($user);

        Mail::assertSent(PasswordChangedMail::class, function (PasswordChangedMail $mail) use ($user) {
            return $mail->hasTo($user->email) && $mail->user->id === $user->id;
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_sends_exactly_one_email(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $action = new SendPasswordChangedEmailAction();

        $action->execute($user);

        Mail::assertSentCount(1);
    }
}
