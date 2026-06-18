<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
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
}
