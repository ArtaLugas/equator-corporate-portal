<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyDocument extends Model
{
    use HasTranslations, SoftDeletes;

    /**
     * Only NON-translatable columns. The HasTranslations trait appends the
     * localized columns (title_en/_id, description_en/_id) and Purifier-sanitizes
     * the HTML field (description) for every locale on write.
     */
    public $fillable = [
        'slug',
        'file',
        'thumbnail',
        'document_type',
        'file_size',
        'download_count',
        'display_order',
        'status',
    ];
}
