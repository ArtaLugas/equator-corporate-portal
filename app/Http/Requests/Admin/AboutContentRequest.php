<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesTranslations;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Validation for creating/updating an About Content block.
 *
 * User-facing `title` and `content` are multilingual; both are optional (a
 * "lead" block may have content but no title), so no field is forced required in
 * the default locale. The `key` is a STABLE machine identifier derived from the
 * DEFAULT-locale title (never translated). The i18n policy (incl. all-or-nothing)
 * lives in the shared ValidatesTranslations trait.
 */
class AboutContentRequest extends FormRequest
{
    use ValidatesTranslations;

    /** Translatable base field => max length (null = unbounded text/longText). */
    private const SPECS = [
        'title' => 191,
        'content' => null,
    ];

    protected function translatableSpecs(): array
    {
        return self::SPECS;
    }

    /** Title is optional here — nothing is required in the default locale. */
    protected function requiredInDefaultLocale(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Derive the machine `key` from the default-locale title (read-only field),
     * preserving the existing key when the title is cleared on edit.
     */
    protected function prepareForValidation(): void
    {
        $default = config('locales.default');
        $content = $this->route('about_content');

        $this->merge([
            'key' => $this->generateKey($this->input("title_{$default}"), $content?->key),
        ]);
    }

    public function rules(): array
    {
        $content = $this->route('about_content');

        return array_merge($this->translatableRules(), [
            'section_id' => ['required', 'exists:about_sections,id'],
            'key' => [
                'nullable', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/',
                Rule::unique('about_contents')
                    ->where(fn ($query) => $query->where('section_id', $this->section_id))
                    ->ignore($content?->id),
            ],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'display_order' => [
                'required', 'integer', 'min:1',
                Rule::unique('about_contents')
                    ->where(fn ($query) => $query->where('section_id', $this->section_id))
                    ->ignore($content?->id),
            ],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }

    /**
     * Build the identifier `key` from a title source (slug with underscores),
     * falling back to a provided value (e.g. the old key) when the title is blank.
     */
    private function generateKey(?string $title, ?string $fallback = null): ?string
    {
        $source = filled($title) ? $title : $fallback;

        if (blank($source)) {
            return null;
        }

        return Str::slug(trim($source), '_');
    }
}
