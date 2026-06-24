<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeroBanner extends Model
{
    use HasFactory, HasTranslations;

    /**
     * Only NON-translatable columns. The HasTranslations trait appends the
     * localized columns (title_en/_id, subtitle_en/_id, button_text_en/_id)
     * from config/translatable.php.
     */
    protected $fillable = [
        'image',
        'button_link',
        'display_order',
        'status',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order');
    }
}
