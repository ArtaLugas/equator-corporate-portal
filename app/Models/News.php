<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
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

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /**
     * Publicly visible articles: published AND past their publish time.
     *
     * A future `published_at` embargoes the article — this is what makes the
     * admin "Publish Date" a real scheduled-publishing control instead of just a
     * display date. A null publish date is treated as immediately visible so
     * legacy/seed rows without a date are never hidden. Admin listings do NOT
     * use this scope, so editors still see scheduled (future) articles.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(function (Builder $q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
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
