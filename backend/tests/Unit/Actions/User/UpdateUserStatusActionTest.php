<?php

namespace Tests\Unit\Actions\User;

use App\Actions\Audit\LogAuditAction;
use App\Actions\User\UpdateUserStatusAction;
use App\DTOs\User\UpdateUserStatusDTO;
use App\Events\UserStatusChangedEvent;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\User\InvalidStatusTransitionException;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class UpdateUserStatusActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateUserStatusAction $action;

    private User $admin;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UpdateUserStatusAction::class);
        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->user = User::factory()->create(['status' => 'active']);
    }

    public function test_updates_user_status_from_active_to_suspended(): void
    {
        Event::fake();

        $dto = new UpdateUserStatusDTO(
            user: $this->user,
            new_status: 'suspended',
            admin: $this->admin,
            reason: 'Policy violation'
        );

        $result = $this->action->execute($dto);

        $this->assertEquals('suspended', $result->status);
        $this->assertEquals('suspended', $this->user->fresh()->status);

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditLog::ACTION_STATUS_CHANGED,
            'auditable_type' => User::class,
            'auditable_id' => $this->user->id,
        ]);

        Event::assertDispatched(UserStatusChangedEvent::class, function ($event) {
            return $event->user->id === $this->user->id
                && $event->oldStatus === 'active'
                && $event->newStatus === 'suspended';
        });
    }

    public function test_updates_user_status_from_suspended_to_active(): void
    {
        Event::fake();
        $this->user->update(['status' => 'suspended']);

        $dto = new UpdateUserStatusDTO(
            user: $this->user,
            new_status: 'active',
            admin: $this->admin,
            reason: 'Issue resolved'
        );

        $result = $this->action->execute($dto);

        $this->assertEquals('active', $result->status);
        $this->assertEquals('active', $this->user->fresh()->status);

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditLog::ACTION_STATUS_CHANGED,
            'auditable_id' => $this->user->id,
        ]);
    }

    public function test_throws_exception_for_unchanged_status(): void
    {
        $this->expectException(InvalidStatusTransitionException::class);

        $dto = new UpdateUserStatusDTO(
            user: $this->user, // already active
            new_status: 'active',
            admin: $this->admin
        );

        $this->action->execute($dto);

        // No audit log should be created
        $this->assertDatabaseMissing('audit_logs', [
            'auditable_id' => $this->user->id,
        ]);
    }

    public function test_throws_exception_for_non_admin(): void
    {
        $this->expectException(UnauthorizedException::class);

        $nonAdmin = User::factory()->create(['role' => 'customer']);

        $dto = new UpdateUserStatusDTO(
            user: $this->user,
            new_status: 'suspended',
            admin: $nonAdmin
        );

        $this->action->execute($dto);
    }

    public function test_creates_audit_log_with_reason(): void
    {
        Event::fake();

        $reason = 'Terms of service violation';

        $dto = new UpdateUserStatusDTO(
            user: $this->user,
            new_status: 'suspended',
            admin: $this->admin,
            reason: $reason
        );

        $this->action->execute($dto);

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditLog::ACTION_STATUS_CHANGED,
            'auditable_id' => $this->user->id,
            'reason' => $reason,
        ]);

        $log = AuditLog::where('auditable_id', $this->user->id)->first();
        $this->assertEquals(['status' => 'active'], $log->old_values);
        $this->assertEquals(['status' => 'suspended'], $log->new_values);
    }

    public function test_wraps_operations_in_transaction(): void
    {
        Event::fake();

        // Mock LogAuditAction to throw an exception inside the transaction
        $this->mock(LogAuditAction::class, function ($mock) {
            $mock->shouldReceive('execute')->andThrow(new \RuntimeException('DB failure'));
        });

        $action = app(UpdateUserStatusAction::class);

        $dto = new UpdateUserStatusDTO(
            user: $this->user,
            new_status: 'suspended',
            admin: $this->admin
        );

        $this->expectException(\RuntimeException::class);
        $action->execute($dto);

        // Status should remain unchanged because transaction was rolled back
        $this->assertEquals('active', $this->user->fresh()->status);
    }

    public function test_dispatches_user_status_changed_event(): void
    {
        Event::fake();

        $dto = new UpdateUserStatusDTO(
            user: $this->user,
            new_status: 'suspended',
            admin: $this->admin
        );

        $this->action->execute($dto);

        Event::assertDispatched(UserStatusChangedEvent::class, function ($event) {
            return $event->user->id === $this->user->id
                && $event->admin->id === $this->admin->id;
        });
    }
}
