<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AboutContent extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    /**
     * Only NON-translatable columns. The HasTranslations trait appends the
     * localized columns (title_en, content_en, …) and Purifier-sanitizes the
     * HTML field (content) for every locale on write — replacing the old
     * content() mutator. `key` is a stable machine identifier (derived from the
     * default-locale title) and is intentionally NOT translated.
     */
    protected $fillable = [
        'section_id',
        'key',
        'image',
        'display_order',
        'status',
    ];

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Filter konten berdasarkan identifier mesin yang stabil.
     * Contoh: AboutContent::key('vision')->first()
     */
    public function scopeKey(Builder $query, string $key)
    {
        return $query->where('key', $key);
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIP
    |--------------------------------------------------------------------------
    */

    public function section(): BelongsTo
    {
        return $this->belongsTo(
            AboutSection::class,
            'section_id'
        );
    }
}
