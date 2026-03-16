<?php

namespace Tests\Unit\Actions\Organisation;

use App\Actions\Audit\LogAuditAction;
use App\Actions\Organisation\UpdateOrganisationStatusAction;
use App\DTOs\Organisation\UpdateOrganisationStatusDTO;
use App\Events\OrganisationStatusChangedEvent;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\User\InvalidStatusTransitionException;
use App\Models\AuditLog;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UpdateOrganisationStatusActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateOrganisationStatusAction $action;

    private User $admin;

    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UpdateOrganisationStatusAction::class);
        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->organisation = Organisation::factory()->create(['status' => 'active']);
    }

    public function test_updates_organisation_status_from_active_to_suspended(): void
    {
        Event::fake();

        $dto = new UpdateOrganisationStatusDTO(
            organisation: $this->organisation,
            new_status: 'suspended',
            admin: $this->admin,
            reason: 'Fraudulent activity'
        );

        $result = $this->action->execute($dto);

        $this->assertEquals('suspended', $result->status);
        $this->assertEquals('suspended', $this->organisation->fresh()->status);

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditLog::ACTION_STATUS_CHANGED,
            'auditable_type' => Organisation::class,
            'auditable_id' => $this->organisation->id,
        ]);

        Event::assertDispatched(OrganisationStatusChangedEvent::class, function ($event) {
            return $event->organisation->id === $this->organisation->id
                && $event->oldStatus === 'active'
                && $event->newStatus === 'suspended';
        });
    }

    public function test_updates_organisation_status_from_suspended_to_active(): void
    {
        Event::fake();
        $this->organisation->update(['status' => 'suspended']);

        $dto = new UpdateOrganisationStatusDTO(
            organisation: $this->organisation,
            new_status: 'active',
            admin: $this->admin,
            reason: 'Issue resolved'
        );

        $result = $this->action->execute($dto);

        $this->assertEquals('active', $result->status);
        $this->assertEquals('active', $this->organisation->fresh()->status);
    }

    public function test_throws_exception_for_unchanged_status(): void
    {
        $this->expectException(InvalidStatusTransitionException::class);

        $dto = new UpdateOrganisationStatusDTO(
            organisation: $this->organisation, // already active
            new_status: 'active',
            admin: $this->admin
        );

        $this->action->execute($dto);
    }

    public function test_throws_exception_for_non_admin(): void
    {
        $this->expectException(UnauthorizedException::class);

        $nonAdmin = User::factory()->create(['role' => 'customer']);

        $dto = new UpdateOrganisationStatusDTO(
            organisation: $this->organisation,
            new_status: 'suspended',
            admin: $nonAdmin
        );

        $this->action->execute($dto);
    }

    public function test_creates_audit_log_with_reason(): void
    {
        Event::fake();
        $reason = 'Compliance failure';

        $dto = new UpdateOrganisationStatusDTO(
            organisation: $this->organisation,
            new_status: 'suspended',
            admin: $this->admin,
            reason: $reason
        );

        $this->action->execute($dto);

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditLog::ACTION_STATUS_CHANGED,
            'auditable_id' => $this->organisation->id,
            'reason' => $reason,
        ]);

        $log = AuditLog::where('auditable_id', $this->organisation->id)
            ->where('auditable_type', Organisation::class)
            ->first();
        $this->assertEquals(['status' => 'active'], $log->old_values);
        $this->assertEquals(['status' => 'suspended'], $log->new_values);
    }

    public function test_wraps_operations_in_transaction(): void
    {
        Event::fake();

        $this->mock(LogAuditAction::class, function ($mock) {
            $mock->shouldReceive('execute')->andThrow(new \RuntimeException('DB failure'));
        });

        $action = app(UpdateOrganisationStatusAction::class);

        $dto = new UpdateOrganisationStatusDTO(
            organisation: $this->organisation,
            new_status: 'suspended',
            admin: $this->admin
        );

        $this->expectException(\RuntimeException::class);
        $action->execute($dto);

        $this->assertEquals('active', $this->organisation->fresh()->status);
    }
}
