<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory, HasTranslations;

    /**
     * Only NON-translatable columns. The HasTranslations trait appends the
     * localized columns (question_en/_id, answer_en/_id) from
     * config/translatable.php. `answer` is plain text (not an HTML field).
     */
    protected $fillable = [
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
                foreach (array_keys(config('locales.supported', [])) as $locale) {
                    $inner->orWhere("question_{$locale}", 'like', "%{$term}%")
                        ->orWhere("answer_{$locale}", 'like', "%{$term}%");
                }
            });
        });
    }
}
