<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    public const ACTION_CREATED = 'created';

    public const ACTION_UPDATED = 'updated';

    public const ACTION_DELETED = 'deleted';

    public const ACTION_STATUS_CHANGED = 'status_changed';

    public const ACTION_ASSIGNED = 'assigned';

    public const ACTION_UNASSIGNED = 'unassigned';

    protected $guarded = ['id'];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the auditable model (the model that was changed).
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForAuditable($query, $model)
    {
        return $query->where('auditable_type', get_class($model))
            ->where('auditable_id', $model->id);
    }

    public function scopeOfAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', now()->toDateString());
    }

    public function scopeByDateRange($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    // Helper methods

    public function getChanges(): array
    {
        $oldValues = $this->old_values ?? [];
        $newValues = $this->new_values ?? [];

        $differences = [];

        // Check for differences in keys that exist in new_values
        foreach ($newValues as $key => $newValue) {
            if (! array_key_exists($key, $oldValues) || $oldValues[$key] !== $newValue) {
                $differences[$key] = $newValue;
            }
        }

        // Check for keys that were deleted (exist in old but not new)
        foreach ($oldValues as $key => $oldValue) {
            if (! array_key_exists($key, $newValues)) {
                $differences[$key] = null; // Represents deletion/removal
            }
        }

        return array_keys($differences);
    }

    public function getChangedValue($field): mixed
    {
        return $this->new_values[$field] ?? null;
    }

    public function getOldValue($field): mixed
    {
        return $this->old_values[$field] ?? null;
    }

    public function hasChanged($field): bool
    {
        return in_array($field, $this->getChanges());
    }
}
