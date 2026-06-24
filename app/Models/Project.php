<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    /** Statuses that are visible on the public site (a "case study" is delivered work). */
    public const PUBLIC_STATUSES = ['ongoing', 'completed'];

    /**
     * Only NON-translatable columns. The HasTranslations trait appends the
     * localized columns (name_en, name_id, …) from config/translatable.php and
     * centrally Purifier-sanitizes the HTML field (description) for every locale
     * on write — replacing the old description() mutator.
     */
    protected $fillable = [
        'slug',
        'client_name',
        'location',
        'country',
        'start_date',
        'end_date',
        'status',
        'featured_image',
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
}
