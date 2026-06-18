<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;

    protected $fillable = [
        'question',
        'answer',
        'display_order',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

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
                $inner->where('question', 'like', "%{$term}%")
                    ->orWhere('answer', 'like', "%{$term}%");
            });
        });
    }
}
