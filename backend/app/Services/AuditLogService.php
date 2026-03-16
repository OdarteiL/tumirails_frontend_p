<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditLogService
{
    /**
     * Get paginated audit logs for any auditable model with optional filters.
     *
     * @param  Model  $auditable  The model whose audit logs to retrieve
     * @param  array  $filters  Recognized keys: action, date_from, date_to, per_page
     */
    public function getLogsForAuditable(Model $auditable, array $filters = []): LengthAwarePaginator
    {
        return AuditLog::query()
            ->forAuditable($auditable)
            ->when($filters['action'] ?? null, fn ($q, $action) => $q->ofAction($action))
            ->when($filters['date_from'] ?? null, fn ($q, $from) => $q->where('created_at', '>=', $from))
            ->when($filters['date_to'] ?? null, fn ($q, $to) => $q->where('created_at', '<=', $to))
            ->with('user')
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }
}
