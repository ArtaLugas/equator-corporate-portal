<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfficeLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
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
                $inner->where('name', 'like', "%{$term}%")
                    ->orWhere('address', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
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
