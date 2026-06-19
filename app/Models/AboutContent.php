<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mews\Purifier\Facades\Purifier;

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

    /**
     * Sanitize the rich-text body on write. Content is admin-authored via
     * CKEditor and rendered to the public with {!! !!}, so purifying at the
     * source keeps stored HTML trustworthy and closes the stored-XSS vector.
     */
    protected function content(): Attribute
    {
        return Attribute::set(
            fn (?string $value) => $value === null ? null : Purifier::clean($value)
        );
    }

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
