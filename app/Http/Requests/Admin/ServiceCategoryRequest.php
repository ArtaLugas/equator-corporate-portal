<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesTranslations;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for creating/updating a Service Category.
 *
 * The i18n policy (EN required, ID all-or-nothing, summary message, localized
 * attribute labels) lives in the shared ValidatesTranslations trait. This class
 * only declares its own translatable field spec and its non-translatable rules.
 */
class ServiceCategoryRequest extends FormRequest
{
    use ValidatesTranslations;

    /** Translatable base field => max length (null = unbounded text/longText). */
    private const SPECS = [
        'name' => 191,
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
        $category = $this->route('service_category');

        return array_merge($this->translatableRules(), [
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'display_order' => [
                'nullable',
                'integer',
                'min:1',
                'unique:service_categories,display_order'.($category ? ','.$category->id : ''),
            ],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }
}
