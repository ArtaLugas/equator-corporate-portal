<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
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
}
