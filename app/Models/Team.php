<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'position',
        'photo',
        'bio',
        'email',
        'linkedin_url',
        'display_order',
        'status',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];
}
