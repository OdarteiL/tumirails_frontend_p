<?php

namespace Tests\Unit\Actions\Audit;

use App\Actions\Audit\LogAuditAction;
use App\DTOs\Audit\LogAuditDTO;
use App\Models\AuditLog;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class LogAuditActionTest extends TestCase
{
    use RefreshDatabase;

    private LogAuditAction $action;

    private User $admin;

    private User $targetUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(LogAuditAction::class);
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->targetUser = User::factory()->create();
    }

    public function test_creates_audit_log_successfully(): void
    {
        $dto = new LogAuditDTO(
            user: $this->admin,
            auditable: $this->targetUser,
            action: AuditLog::ACTION_STATUS_CHANGED,
            old_values: ['status' => 'active'],
            new_values: ['status' => 'suspended'],
            reason: 'Test reason'
        );

        $log = $this->action->execute($dto);

        $this->assertNotNull($log);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'auditable_type' => User::class,
            'auditable_id' => $this->targetUser->id,
            'action' => AuditLog::ACTION_STATUS_CHANGED,
            'reason' => 'Test reason',
        ]);
    }

    public function test_captures_old_and_new_values(): void
    {
        $dto = new LogAuditDTO(
            user: $this->admin,
            auditable: $this->targetUser,
            action: AuditLog::ACTION_STATUS_CHANGED,
            old_values: ['status' => 'active'],
            new_values: ['status' => 'suspended'],
        );

        $log = $this->action->execute($dto);

        $this->assertEquals(['status' => 'active'], $log->old_values);
        $this->assertEquals(['status' => 'suspended'], $log->new_values);
    }

    public function test_captures_ip_address_from_request(): void
    {
        $request = Request::create('/test', 'POST', [], [], [], [
            'REMOTE_ADDR' => '192.168.1.1',
            'HTTP_USER_AGENT' => 'Test Agent',
        ]);
        app()->instance('request', $request);

        $dto = new LogAuditDTO(
            user: $this->admin,
            auditable: $this->targetUser,
            action: AuditLog::ACTION_STATUS_CHANGED,
        );

        $log = $this->action->execute($dto);

        $this->assertDatabaseHas('audit_logs', [
            'ip_address' => '192.168.1.1',
        ]);
    }

    public function test_captures_user_agent_from_request(): void
    {
        $request = Request::create('/test', 'POST', [], [], [], [
            'REMOTE_ADDR' => '10.0.0.1',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 Test Browser',
        ]);
        app()->instance('request', $request);

        $dto = new LogAuditDTO(
            user: $this->admin,
            auditable: $this->targetUser,
            action: AuditLog::ACTION_STATUS_CHANGED,
        );

        $this->action->execute($dto);

        $this->assertDatabaseHas('audit_logs', [
            'user_agent' => 'Mozilla/5.0 Test Browser',
        ]);
    }

    public function test_stores_reason_when_provided(): void
    {
        $reason = 'Administrative override';

        $dto = new LogAuditDTO(
            user: $this->admin,
            auditable: $this->targetUser,
            action: AuditLog::ACTION_STATUS_CHANGED,
            reason: $reason
        );

        $log = $this->action->execute($dto);

        $this->assertEquals($reason, $log->reason);
        $this->assertDatabaseHas('audit_logs', ['reason' => $reason]);
    }

    public function test_handles_polymorphic_auditable_with_user(): void
    {
        $dto = new LogAuditDTO(
            user: $this->admin,
            auditable: $this->targetUser,
            action: AuditLog::ACTION_STATUS_CHANGED,
        );

        $log = $this->action->execute($dto);

        $this->assertEquals(User::class, $log->auditable_type);
        $this->assertEquals($this->targetUser->id, $log->auditable_id);
    }

    public function test_handles_polymorphic_auditable_with_organisation(): void
    {
        $org = Organisation::factory()->create();

        $dto = new LogAuditDTO(
            user: $this->admin,
            auditable: $org,
            action: AuditLog::ACTION_STATUS_CHANGED,
        );

        $log = $this->action->execute($dto);

        $this->assertEquals(Organisation::class, $log->auditable_type);
        $this->assertEquals($org->id, $log->auditable_id);
    }

    public function test_returns_null_and_logs_error_on_failure(): void
    {
        // Force an exception by passing a model with no ID
        $newUser = new User();

        $dto = new LogAuditDTO(
            user: $this->admin,
            auditable: $newUser, // No ID — will cause FK violation
            action: AuditLog::ACTION_STATUS_CHANGED,
        );

        $log = $this->action->execute($dto);

        $this->assertNull($log);
    }
}
