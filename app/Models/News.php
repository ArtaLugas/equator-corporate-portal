<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class News extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    /**
     * Only NON-translatable columns. The HasTranslations trait appends the
     * localized columns (title_en, title_id, content_en, …) from
     * config/translatable.php and Purifier-sanitizes the HTML field (content)
     * for every locale on write.
     */
    protected $fillable = [
        'category_id',
        'slug',
        'image',
        'status',
        'published_at',
        'views_count',
        'is_featured',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function category()
    {
        return $this->belongsTo(NewsCategory::class, 'category_id');
    }

    public function tags()
    {
        return $this->belongsToMany(
            Tag::class,
            'news_tag',
            'news_id',
            'tag_id'
        );
    }
}
