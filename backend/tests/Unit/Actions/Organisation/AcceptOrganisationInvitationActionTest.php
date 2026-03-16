<?php

namespace Tests\Unit\Actions\Organisation;

use App\Actions\Organisation\AcceptOrganisationInvitationAction;
use App\Models\Organisation;
use App\Models\OrganisationInvitation;
use App\Models\OrganisationMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcceptOrganisationInvitationActionTest extends TestCase
{
    use RefreshDatabase;

    private AcceptOrganisationInvitationAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new AcceptOrganisationInvitationAction();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_accepts_invitation_and_creates_member()
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

        $member = $this->action->execute($invitation->token, $user);

        $this->assertInstanceOf(OrganisationMember::class, $member);
        $this->assertEquals($organisation->id, $member->organisation_id);
        $this->assertEquals($user->id, $member->user_id);
        $this->assertEquals('admin', $member->role);
        $this->assertNotNull($member->joined_at);

        $invitation->refresh();
        $this->assertNotNull($invitation->accepted_at);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_throws_exception_for_invalid_token()
    {
        $user = User::factory()->create();

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->action->execute('invalid-token', $user);
    }

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
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
        $this->expectExceptionMessage('This invitation is no longer valid.');
        $this->action->execute($invitation->token, $user);
    }

    #[\PHPUnit\Framework\Attributes\Test]
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
        $this->expectExceptionMessage('This invitation is no longer valid.');
        $this->action->execute($invitation->token, $user);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_throws_exception_if_user_is_already_member()
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();

        OrganisationMember::create([
            'organisation_id' => $organisation->id,
            'user_id' => $user->id,
            'role' => 'member',
            'joined_at' => now(),
        ]);

        $invitation = OrganisationInvitation::create([
            'organisation_id' => $organisation->id,
            'email' => $user->email,
            'role' => 'admin',
            'token' => OrganisationInvitation::generateToken(),
            'invited_by' => User::factory()->create()->id,
            'expires_at' => now()->addDays(7),
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You are already a member of this organisation.');
        $this->action->execute($invitation->token, $user);
    }

    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_loads_organisation_and_user_relations()
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

        $member = $this->action->execute($invitation->token, $user);

        $this->assertTrue($member->relationLoaded('organisation'));
        $this->assertTrue($member->relationLoaded('user'));
        $this->assertEquals($organisation->id, $member->organisation->id);
        $this->assertEquals($user->id, $member->user->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_assigns_correct_role_from_invitation()
    {
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();

        $roles = ['admin', 'member'];

        foreach ($roles as $role) {
            $invitation = OrganisationInvitation::create([
                'organisation_id' => $organisation->id,
                'email' => $user->email,
                'role' => $role,
                'token' => OrganisationInvitation::generateToken(),
                'invited_by' => User::factory()->create()->id,
                'expires_at' => now()->addDays(7),
            ]);

            $member = $this->action->execute($invitation->token, $user);
            $this->assertEquals($role, $member->role);

            // Clean up for next iteration
            $member->delete();
        }
    }
}
