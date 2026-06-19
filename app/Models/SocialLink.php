<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'platform',
        'url',
        'icon_class',
        'display_order',
        'status',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * Brand hex color from config — used as hover color in the footer.
     * Falls back to white so the icon is always visible on dark backgrounds.
     */
    protected function brandColor(): Attribute
    {
        return Attribute::get(
            fn () => config('social_platforms.'.strtolower(trim($this->platform)).'.color', '#ffffff')
        );
    }

    /**
     * Human-readable platform label for aria-label / title attributes.
     * Falls back to the raw platform value if not found in config.
     */
    protected function brandLabel(): Attribute
    {
        return Attribute::get(
            fn () => config('social_platforms.'.strtolower(trim($this->platform)).'.label', $this->platform)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, function ($q) use ($term) {
            $term = trim($term);
            $q->where(function ($inner) use ($term) {
                $inner->where('platform', 'like', "%{$term}%")
                    ->orWhere('url', 'like', "%{$term}%");
            });
        });
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return $query->when(
            in_array($status, ['active', 'inactive'], true),
            fn ($q) => $q->where('status', $status)
        );
    }
}
