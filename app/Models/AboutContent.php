<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AboutContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'key',
        'title',
        'content',
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
