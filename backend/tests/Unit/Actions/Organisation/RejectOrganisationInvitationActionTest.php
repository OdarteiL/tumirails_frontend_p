<?php

namespace Tests\Unit\Actions\Organisation;

use App\Actions\Organisation\RejectOrganisationInvitationAction;
use App\Models\Organisation;
use App\Models\OrganisationInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RejectOrganisationInvitationActionTest extends TestCase
{
    use RefreshDatabase;

    private RejectOrganisationInvitationAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new RejectOrganisationInvitationAction();
    }

    /** @test */
    public function it_rejects_invitation_successfully()
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();

        $invitation = OrganisationInvitation::create([
            'organisation_id' => $organisation->id,
            'email' => $user->email,
            'role' => 'admin',
            'token' => OrganisationInvitation::generateToken(),
            'invited_by' => User::factory()->create()->id,
            'expires_at' => now()->addDays(7),
        ]);

        $result = $this->action->execute($invitation->token, $user);

        $this->assertInstanceOf(OrganisationInvitation::class, $result);
        $this->assertNotNull($result->rejected_at);

        $invitation->refresh();
        $this->assertNotNull($invitation->rejected_at);
    }

    /** @test */
    public function it_throws_exception_for_invalid_token()
    {
        $user = User::factory()->create();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->action->execute('invalid-token', $user);
    }

    /** @test */
    public function it_throws_exception_for_expired_invitation()
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();

        $invitation = OrganisationInvitation::create([
            'organisation_id' => $organisation->id,
            'email' => $user->email,
            'role' => 'admin',
            'token' => OrganisationInvitation::generateToken(),
            'invited_by' => User::factory()->create()->id,
            'expires_at' => now()->subDay(),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This invitation is no longer valid.');
        $this->action->execute($invitation->token, $user);
    }

    /** @test */
    public function it_throws_exception_for_already_accepted_invitation()
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();

        $invitation = OrganisationInvitation::create([
            'organisation_id' => $organisation->id,
            'email' => $user->email,
            'role' => 'admin',
            'token' => OrganisationInvitation::generateToken(),
            'invited_by' => User::factory()->create()->id,
            'expires_at' => now()->addDays(7),
            'accepted_at' => now(),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This invitation has already been accepted.');
        $this->action->execute($invitation->token, $user);
    }

    /** @test */
    public function it_throws_exception_for_already_rejected_invitation()
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();

        $invitation = OrganisationInvitation::create([
            'organisation_id' => $organisation->id,
            'email' => $user->email,
            'role' => 'admin',
            'token' => OrganisationInvitation::generateToken(),
            'invited_by' => User::factory()->create()->id,
            'expires_at' => now()->addDays(7),
            'rejected_at' => now(),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('This invitation has already been rejected.');
        $this->action->execute($invitation->token, $user);
    }

    /** @test */
    public function it_throws_exception_for_mismatched_email()
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $organisation = Organisation::factory()->create();

        $invitation = OrganisationInvitation::create([
            'organisation_id' => $organisation->id,
            'email' => 'other@example.com',
            'role' => 'admin',
            'token' => OrganisationInvitation::generateToken(),
            'invited_by' => User::factory()->create()->id,
            'expires_at' => now()->addDays(7),
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->action->execute($invitation->token, $user);
    }

    /** @test */
    public function it_loads_organisation_relation()
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();

        $invitation = OrganisationInvitation::create([
            'organisation_id' => $organisation->id,
            'email' => $user->email,
            'role' => 'admin',
            'token' => OrganisationInvitation::generateToken(),
            'invited_by' => User::factory()->create()->id,
            'expires_at' => now()->addDays(7),
        ]);

        $result = $this->action->execute($invitation->token, $user);

        $this->assertTrue($result->relationLoaded('organisation'));
        $this->assertEquals($organisation->id, $result->organisation->id);
    }
}
