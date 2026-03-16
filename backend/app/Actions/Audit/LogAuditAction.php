<?php

namespace App\Actions\Audit;

use App\DTOs\Audit\LogAuditDTO;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Log;

class LogAuditAction
{
    /**
     * Log an audit entry for any model change.
     * Captures request context automatically if available.
     *
     * @return AuditLog|null Returns the created log, or null if it fails
     */
    public function execute(LogAuditDTO $dto): ?AuditLog
    {
        try {
            $ipAddress = rescue(fn () => request()->ip(), null, false);
            $userAgent = rescue(fn () => request()->userAgent(), null, false);

            return AuditLog::create([
                'user_id' => $dto->user->id,
                'auditable_type' => get_class($dto->auditable),
                'auditable_id' => $dto->auditable->getKey(), // Use getKey() to ensure correct ID is fetched
                'action' => $dto->action,
                'old_values' => $dto->old_values,
                'new_values' => $dto->new_values,
                'reason' => $dto->reason,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);
        } catch (\Throwable $e) {
            // Log creation should never fail silently to the terminal, but don't block the main operation block either.
            Log::channel('single')->error('Failed to create audit log', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'dto' => [
                    'user_id' => $dto->user->id ?? null,
                    'auditable_type' => get_class($dto->auditable),
                    'action' => $dto->action,
                ],
            ]);

            return null; // Don't block the main process if audit logging fails
        }
    }
}
