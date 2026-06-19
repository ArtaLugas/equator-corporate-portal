<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    use MassPrunable;

    /**
     * Analytics rows older than this are pruned by the scheduled
     * `model:prune` command (see routes/console.php). Keeps the table
     * bounded so it stays fast and small as traffic grows.
     */
    public const RETENTION_DAYS = 90;

    public $timestamps = false;

    protected $fillable = [
        'ip_address',
        'url',
        'referer',
        'user_agent',
        'session_id',
        'visited_at',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
    ];

    /**
     * The query that finds prunable (expired) analytics rows.
     */
    public function prunable(): Builder
    {
        return static::where('visited_at', '<', now()->subDays(self::RETENTION_DAYS));
    }
}
