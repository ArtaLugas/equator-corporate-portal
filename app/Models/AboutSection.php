<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AboutSection extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    /**
     * Only NON-translatable columns. The HasTranslations trait appends the
     * localized columns (name_en, name_id) from config/translatable.php. The
     * `slug` is an internal identifier generated from the default-locale name
     * and is intentionally NOT translated.
     */
    protected $fillable = [
        'slug',
        'display_order',
        'status',
    ];

    /** Consistent default with the other content tables (the column itself has none). */
    protected $attributes = [
        'status' => 'active',
        'display_order' => 1,
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Semua konten milik section ini (vision, mission, intro, dll).
     */
    public function contents(): HasMany
    {
        return $this->hasMany(AboutContent::class, 'section_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Ambil satu konten berdasarkan title dari relasi yang SUDAH di-load.
     * Bersifat case-insensitive dan tidak menimbulkan query tambahan.
     */
    public function contentByTitle(string $title): ?AboutContent
    {
        return $this->contents->first(
            fn (AboutContent $content) => strtolower(trim((string) $content->title)) === strtolower(trim($title))
        );
    }

    /**
     * Ambil satu konten berdasarkan identifier mesin (key) yang stabil,
     * dari relasi yang SUDAH di-load. Tidak menimbulkan query tambahan.
     */
    public function contentByKey(string $key): ?AboutContent
    {
        return $this->contents->firstWhere('key', $key);
    }
}
