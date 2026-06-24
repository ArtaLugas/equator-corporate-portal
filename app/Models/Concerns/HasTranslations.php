<?php

namespace App\Models\Concerns;

use Mews\Purifier\Facades\Purifier;

/**
 * Suffix-column translations (e.g. name_en, name_id) driven entirely by
 * config/translatable.php and config/locales.php.
 *
 * Models keep locale-agnostic reads:  $service->name  resolves to the active
 * locale, falling back to the default locale when the translation is blank.
 * No view, controller, or model ever references a `_en` / `_id` column name.
 *
 * Usage in a model:
 *
 *     use App\Models\Concerns\HasTranslations;
 *
 *     class Service extends Model
 *     {
 *         use HasTranslations;
 *
 *         // List only NON-translatable columns; the trait appends the
 *         // <field>_<locale> columns to $fillable automatically.
 *         protected $fillable = ['category_id', 'slug', 'image', 'status'];
 *     }
 *
 * Fields are read from config('translatable.<table>'). A model may override by
 * declaring `protected array $translatable` / `protected array $translatableHtml`.
 */
trait HasTranslations
{
    /**
     * Translatable field names for this model (base names, without locale suffix).
     */
    public function translatableFields(): array
    {
        return $this->translatable
            ?? config('translatable.'.$this->getTable().'.fields', []);
    }

    /**
     * Subset of translatable fields that hold rich text and are sanitized on write.
     */
    public function translatableHtmlFields(): array
    {
        return $this->translatableHtml
            ?? config('translatable.'.$this->getTable().'.html', []);
    }

    /**
     * Every concrete localized column, e.g. ['name_en', 'name_id', ...].
     */
    public function translatableColumns(): array
    {
        $locales = array_keys(config('locales.supported', []));
        $columns = [];

        foreach ($this->translatableFields() as $field) {
            foreach ($locales as $locale) {
                $columns[] = "{$field}_{$locale}";
            }
        }

        return $columns;
    }

    /**
     * Expand $fillable with the localized columns so forms can mass-assign them
     * without listing every `_en` / `_id` variant by hand.
     */
    public function getFillable(): array
    {
        return array_values(array_unique(
            array_merge($this->fillable, $this->translatableColumns())
        ));
    }

    /**
     * Resolve a translatable field for a locale, falling back to the default
     * locale when the requested translation is null or empty.
     */
    public function translate(string $field, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        $value = $this->attributes["{$field}_{$locale}"] ?? null;

        if ($value === null || $value === '') {
            $default = config('locales.default');

            if ($locale !== $default) {
                $value = $this->attributes["{$field}_{$default}"] ?? null;
            }
        }

        return $value;
    }

    /**
     * Magic read: `$model->name` returns the active-locale value.
     *
     * Only declared translatable base names are intercepted, so real columns,
     * relations, casts, and appended/accessor attributes are never shadowed
     * (their names are not in the translatable registry). Interception is
     * unconditional for those base names — even while the legacy single-language
     * column still exists during the Phase A/B migration window — so locale
     * resolution always wins over the soon-to-be-dropped legacy column.
     *
     * Note: a field declared translatable must NOT also define a native
     * get{Field}Attribute() accessor — this trait IS its resolver.
     */
    public function getAttribute($key)
    {
        if (is_string($key) && in_array($key, $this->translatableFields(), true)) {
            return $this->translate($key);
        }

        return parent::getAttribute($key);
    }

    /**
     * Magic write: purify HTML translatable columns on set, for every locale
     * (e.g. both content_en and content_id), replacing per-model mutators.
     */
    public function setAttribute($key, $value)
    {
        if (is_string($value) && $this->isTranslatableHtmlColumn($key)) {
            $value = Purifier::clean($value);
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Search across every locale column of the given base fields.
     *
     *     Service::searchTranslatable($term, ['name', 'short_description'])
     */
    public function scopeSearchTranslatable($query, ?string $term, array $fields)
    {
        if (blank($term)) {
            return $query;
        }

        $locales = array_keys(config('locales.supported', []));

        return $query->where(function ($q) use ($term, $fields, $locales) {
            foreach ($fields as $field) {
                foreach ($locales as $locale) {
                    $q->orWhere("{$field}_{$locale}", 'like', "%{$term}%");
                }
            }
        });
    }

    /**
     * Translation completeness of a locale, measured against the fields that
     * actually have content in the default locale (you can only translate what
     * exists in the source). Returns the breakdown the CMS indicator needs:
     *
     *     ['translated' => 3, 'source' => 5, 'percent' => 60]
     *
     * The default locale is its own source, so it always reports 100%.
     */
    public function translationStat(string $locale): array
    {
        $default = config('locales.default');

        $source = 0;
        $translated = 0;

        foreach ($this->translatableFields() as $field) {
            $base = $this->attributes["{$field}_{$default}"] ?? null;

            if ($base === null || $base === '') {
                continue; // nothing to translate for this field
            }

            $source++;

            $value = $locale === $default
                ? $base
                : ($this->attributes["{$field}_{$locale}"] ?? null);

            if ($value !== null && $value !== '') {
                $translated++;
            }
        }

        return [
            'translated' => $translated,
            'source' => $source,
            'percent' => $source === 0 ? 100 : (int) round($translated / $source * 100),
        ];
    }

    /**
     * Translation completeness of a locale as a 0–100 percentage.
     */
    public function translationProgress(string $locale): int
    {
        return $this->translationStat($locale)['percent'];
    }

    /**
     * True when a locale's translation is fully complete relative to the source.
     */
    public function isTranslated(string $locale): bool
    {
        return $this->translationProgress($locale) === 100;
    }

    /**
     * Is $key a localized column (e.g. content_id) of an HTML field?
     */
    protected function isTranslatableHtmlColumn(string $key): bool
    {
        $locales = array_keys(config('locales.supported', []));

        foreach ($locales as $locale) {
            $suffix = "_{$locale}";

            if (str_ends_with($key, $suffix)) {
                $base = substr($key, 0, -strlen($suffix));

                return in_array($base, $this->translatableHtmlFields(), true);
            }
        }

        return false;
    }
}
