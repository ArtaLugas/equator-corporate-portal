<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mews\Purifier\Facades\Purifier;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'short_description',
        'description',
        'image',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'status',
        'is_featured',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
    ];

    /**
     * Sanitize the rich-text body on write — rendered to the public with
     * {!! !!} on the service page, so purifying at the source closes stored XSS.
     */
    protected function description(): Attribute
    {
        return Attribute::set(
            fn (?string $value) => $value === null ? null : Purifier::clean($value)
        );
    }

    /*
    |-----------------------------------------------------------
    |   Relationship
    |-----------------------------------------------------------
    */

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }
}
