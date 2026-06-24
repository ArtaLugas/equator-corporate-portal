<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    /**
     * Only NON-translatable columns. The HasTranslations trait appends the
     * localized columns (name_en, name_id, …) from config/translatable.php to
     * $fillable, and centrally Purifier-sanitizes the HTML field (description)
     * for every locale on write — replacing the old description() mutator.
     */
    protected $fillable = [
        'category_id',
        'slug',
        'image',
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
