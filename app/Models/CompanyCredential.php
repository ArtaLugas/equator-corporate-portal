<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyCredential extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    /**
     * Only NON-translatable columns. The HasTranslations trait appends the
     * localized columns (title_en/_id, issuer_en/_id, description_en/_id) from
     * config/translatable.php and Purifier-sanitizes the HTML field
     * (description) for every locale on write.
     */
    protected $fillable = [
        'category',
        'credential_number',
        'issue_date',
        'expiry_date',
        'image',
        'attachment',
        'verification_url',
        'featured',
        'status',
        'display_order',
        'slug',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'featured' => 'boolean',
        'display_order' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function items(): HasMany
    {
        return $this->hasMany(CompanyCredentialItem::class, 'credential_id')
            ->orderBy('display_order');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('display_order')->orderByDesc('id');
    }

    public function scopeCategory(Builder $query, ?string $category): Builder
    {
        return $query->when($category, fn ($q) => $q->where('category', $category));
    }

    /**
     * Admin search across translatable title/issuer (every locale) + the
     * single-column credential_number.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = trim($term);
        $locales = array_keys(config('locales.supported', []));

        return $query->where(function (Builder $w) use ($term, $locales) {
            foreach (['title', 'issuer'] as $field) {
                foreach ($locales as $locale) {
                    $w->orWhere("{$field}_{$locale}", 'like', "%{$term}%");
                }
            }
            $w->orWhere('credential_number', 'like', "%{$term}%");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | STATUS BADGE (expiry-aware)
    |--------------------------------------------------------------------------
    */

    /**
     * Display status for the badge:
     *   'inactive'      → admin-disabled (never public)
     *   'expired'       → expiry_date is in the past
     *   'expiring_soon' → expiry within config('credentials.expiring_soon_days')
     *   'active'        → no expiry, or expiry comfortably in the future
     */
    public function displayStatus(): string
    {
        if ($this->status !== 'active') {
            return 'inactive';
        }

        if (! $this->expiry_date) {
            return 'active';
        }

        $daysLeft = now()->startOfDay()->diffInDays($this->expiry_date->startOfDay(), false);

        if ($daysLeft < 0) {
            return 'expired';
        }

        if ($daysLeft <= (int) config('credentials.expiring_soon_days', 30)) {
            return 'expiring_soon';
        }

        return 'active';
    }

    /** Localized category label from lang/{locale}/credentials.php (fallback: humanized key). */
    public function categoryLabel(): string
    {
        $key = 'credentials.categories.'.$this->category;
        $label = __($key);

        return $label === $key ? str(str_replace('_', ' ', (string) $this->category))->title()->value() : $label;
    }
}
