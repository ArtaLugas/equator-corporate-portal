<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeyMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'icon',
        'value',
        'label',
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
        return $query->when($term, function ($q) use ($term) {
            $term = trim($term);
            $q->where('label', 'like', "%{$term}%")->orWhere('value', 'like', "%{$term}%");
        });
    }
}
