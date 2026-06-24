<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesTranslations;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for creating/updating a Project.
 *
 * The i18n policy (EN required, ID all-or-nothing, summary message, localized
 * attribute labels) lives in the shared ValidatesTranslations trait. This class
 * declares its own translatable field spec plus the Project-specific
 * (non-translatable) rules: service scope, client / location / dates, status,
 * featured image, and the gallery.
 */
class ProjectRequest extends FormRequest
{
    use ValidatesTranslations;

    /** Translatable base field => max length (null = unbounded text/longText). */
    private const SPECS = [
        'name' => 191,
        'short_description' => 255,
        'description' => null,
        'meta_title' => 191,
        'meta_description' => 320,
        'meta_keywords' => 255,
    ];

    protected function translatableSpecs(): array
    {
        return self::SPECS;
    }

    public function authorize(): bool
    {
        // Route is already behind the admin.auth middleware.
        return true;
    }

    public function rules(): array
    {
        return array_merge($this->translatableRules(), [
            // Service scope (many-to-many)
            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => ['integer', 'exists:services,id'],

            // Project meta (not translated)
            'client_name' => ['nullable', 'string', 'max:191'],
            'location' => ['nullable', 'string', 'max:191'],
            'country' => ['nullable', 'string', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', 'in:planned,ongoing,completed'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'is_featured' => ['nullable', 'boolean'],

            // Gallery — new uploads
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            // Gallery — existing rows metadata
            'images' => ['nullable', 'array'],
            'images.*.caption' => ['nullable', 'string', 'max:191'],
            'images.*.display_order' => ['nullable', 'integer', 'min:0'],

            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => ['integer'],
        ]);
    }
}
