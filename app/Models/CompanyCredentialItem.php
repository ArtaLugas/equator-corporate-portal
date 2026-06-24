<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyCredentialItem extends Model
{
    use HasFactory, HasTranslations;

    /**
     * Only NON-translatable columns. The HasTranslations trait appends the
     * localized columns (title_en/_id, description_en/_id). Both are plain text
     * (no HTML field declared), so nothing is Purifier-sanitized here.
     */
    protected $fillable = [
        'credential_id',
        'display_order',
    ];

    protected $casts = [
        'display_order' => 'integer',
    ];

    public function credential(): BelongsTo
    {
        return $this->belongsTo(CompanyCredential::class, 'credential_id');
    }
}
