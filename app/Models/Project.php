<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

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
}
