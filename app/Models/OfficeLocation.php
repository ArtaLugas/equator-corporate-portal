<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeLocation extends Model
{
    use HasFactory, HasTranslations;

    /**
     * Only NON-translatable columns. The HasTranslations trait appends the
     * localized columns (name_en, name_id, address_en, address_id) from
     * config/translatable.php.
     */
    protected $fillable = [
        'phone',
        'email',
        'map_embed',
        'is_primary',
        'display_order',
        'status',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'display_order' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('is_primary')->orderBy('display_order')->orderBy('id');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when($term, function ($q) use ($term) {
            $term = trim($term);
            $q->where(function ($inner) use ($term) {
                // name & address are translatable — search every locale column.
                foreach (array_keys(config('locales.supported', [])) as $locale) {
                    $inner->orWhere("name_{$locale}", 'like', "%{$term}%")
                        ->orWhere("address_{$locale}", 'like', "%{$term}%");
                }
                $inner->orWhere('email', 'like', "%{$term}%");
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
