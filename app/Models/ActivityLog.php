<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use MassPrunable;

    const UPDATED_AT = null;

    protected $fillable = [

        'admin_id',

        'module',

        'description',

        'ip_address',

    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIP
    |--------------------------------------------------------------------------
    */

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    /*
    |--------------------------------------------------------------------------
    | PRUNING (retention)
    |--------------------------------------------------------------------------
    | The scheduled `model:prune` command (routes/console.php) deletes audit-log
    | rows older than the configured retention window. Time-based only — there is
    | deliberately no manual "clear log" action, since that would let an admin
    | erase their own trail. MassPrunable deletes in bulk (no per-row events).
    */

    public function prunable(): Builder
    {
        $days = (int) config('cms.activity_log_retention_days', 365);

        // 0 (or below) → retain indefinitely: match nothing so nothing is pruned.
        if ($days <= 0) {
            return static::query()->whereRaw('1 = 0');
        }

        return static::query()->where('created_at', '<', now()->subDays($days));
    }
}
