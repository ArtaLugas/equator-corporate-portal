<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AboutHistory extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    /**
     * Only NON-translatable columns. The HasTranslations trait appends the
     * localized columns (title_en/_id, description_en/_id) and Purifier-sanitizes
     * the HTML field (description) for every locale on write — replacing the old
     * description() mutator.
     */
    protected $fillable = [
        'year',
        'image',
        'display_order',
        'status',
    ];

    protected $casts = [
        'year' => 'integer',
        'display_order' => 'integer',
    ];
}
