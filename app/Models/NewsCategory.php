<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsCategory extends Model
{
    use HasFactory, HasTranslations;

    // Only NON-translatable columns are listed; the HasTranslations trait appends
    // the localized name columns (name_en, name_id) to $fillable automatically.
    protected $fillable = [
        'slug',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function news()
    {
        return $this->hasMany(News::class, 'category_id');
    }
}
