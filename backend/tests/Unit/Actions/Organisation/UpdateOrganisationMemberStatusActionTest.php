<?php

namespace Tests\Unit\Actions\Organisation;

use App\Actions\Organisation\UpdateOrganisationMemberStatusAction;
use App\DTOs\Organisation\UpdateOrganisationMemberStatusDTO;
use App\Events\OrganisationMemberStatusChangedEvent;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\User\InvalidStatusTransitionException;
use App\Models\AuditLog;
use App\Models\Organisation;
use App\Models\OrganisationMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UpdateOrganisationMemberStatusActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateOrganisationMemberStatusAction $action;

    private User $admin;

    private Organisation $organisation;

    private OrganisationMember $member;

    private User $memberUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UpdateOrganisationMemberStatusAction::class);
        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->organisation = Organisation::factory()->create(['status' => 'active']);

        $this->memberUser = User::factory()->create(['status' => 'active']);
        $this->member = OrganisationMember::factory()->create([
            'organisation_id' => $this->organisation->id,
            'user_id' => $this->memberUser->id,
            'role' => 'member',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_suspend_member(): void
    {
        Event::fake();

        $dto = new UpdateOrganisationMemberStatusDTO(
            member: $this->member,
            new_status: 'suspended',
            performer: $this->admin,
            reason: 'Inactivity'
        );

        $result = $this->action->execute($dto);

        $this->assertEquals('suspended', $result->status);
        $this->assertEquals('suspended', $this->member->fresh()->status);

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditLog::ACTION_STATUS_CHANGED,
            'auditable_type' => OrganisationMember::class,
            'auditable_id' => $this->member->id,
        ]);

        Event::assertDispatched(OrganisationMemberStatusChangedEvent::class, function ($event) {
            return $event->member->id === $this->member->id
                && $event->oldStatus === 'active'
                && $event->newStatus === 'suspended';
        });
    }

    public function test_org_owner_can_suspend_member(): void
    {
        Event::fake();

        $owner = User::factory()->create(['role' => 'installer', 'status' => 'active']);
        OrganisationMember::factory()->create([
            'organisation_id' => $this->organisation->id,
            'user_id' => $owner->id,
            'role' => 'owner',
            'status' => 'active',
        ]);

        $dto = new UpdateOrganisationMemberStatusDTO(
            member: $this->member,
            new_status: 'suspended',
            performer: $owner,
            reason: 'Code of conduct breach'
        );

        $result = $this->action->execute($dto);
        $this->assertEquals('suspended', $result->status);
    }

    public function test_throws_exception_for_unchanged_status(): void
    {
        $this->expectException(InvalidStatusTransitionException::class);

        $dto = new UpdateOrganisationMemberStatusDTO(
            member: $this->member, // already active
            new_status: 'active',
            performer: $this->admin
        );

        $this->action->execute($dto);
    }

    public function test_throws_exception_for_unauthorized_performer(): void
    {
        $this->expectException(UnauthorizedException::class);

        $regularUser = User::factory()->create(['role' => 'customer']);

        $dto = new UpdateOrganisationMemberStatusDTO(
            member: $this->member,
            new_status: 'suspended',
            performer: $regularUser
        );

        $this->action->execute($dto);
    }

    public function test_creates_audit_log_with_reason(): void
    {
        Event::fake();

        $reason = 'Repeated policy violations';

        $dto = new UpdateOrganisationMemberStatusDTO(
            member: $this->member,
            new_status: 'suspended',
            performer: $this->admin,
            reason: $reason
        );

        $this->action->execute($dto);

        $this->assertDatabaseHas('audit_logs', [
            'reason' => $reason,
            'auditable_id' => $this->member->id,
        ]);
    }
}
