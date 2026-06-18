<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
