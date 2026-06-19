<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mews\Purifier\Facades\Purifier;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    /** Statuses that are visible on the public site (a "case study" is delivered work). */
    public const PUBLIC_STATUSES = ['ongoing', 'completed'];

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'client_name',
        'location',
        'country',
        'start_date',
        'end_date',
        'status',
        'featured_image',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'is_featured',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_featured' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }

    public function images()
    {
        return $this->hasMany(ProjectImage::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES & MUTATORS
    |--------------------------------------------------------------------------
    */

    /** Limit to projects that may be shown publicly (excludes `planned`). */
    public function scopePublic(Builder $query): Builder
    {
        return $query->whereIn('status', self::PUBLIC_STATUSES);
    }

    /**
     * Sanitize the rich-text body on write — rendered to the public with
     * {!! !!} on the project page, so purifying at the source closes stored XSS.
     */
    protected function description(): Attribute
    {
        return Attribute::set(
            fn (?string $value) => $value === null ? null : Purifier::clean($value)
        );
    }
}
