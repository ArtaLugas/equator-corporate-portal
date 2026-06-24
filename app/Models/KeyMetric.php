<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeyMetric extends Model
{
    use HasFactory, HasTranslations;

    /**
     * Only NON-translatable columns. The HasTranslations trait appends the
     * localized columns (label_en, label_id) from config/translatable.php.
     */
    protected $fillable = [
        'icon',
        'value',
        'display_order',
        'status',
        'is_featured',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'is_featured' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when(filled($term), function ($q) use ($term) {
            $term = trim($term);
            $q->where(function ($inner) use ($term) {
                $inner->searchTranslatable($term, ['label'])
                    ->orWhere('value', 'like', "%{$term}%");
            });
        });
    }
}
