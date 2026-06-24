<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    /**
     * Only NON-translatable columns. The HasTranslations trait appends the
     * localized columns (position_en/_id, bio_en/_id). A member's `name` is a
     * personal identifier — intentionally single-language. `bio` is plain text.
     */
    protected $fillable = [
        'name',
        'photo',
        'email',
        'linkedin_url',
        'display_order',
        'status',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];
}
