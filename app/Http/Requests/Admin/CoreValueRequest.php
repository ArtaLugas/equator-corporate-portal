<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesTranslations;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for creating/updating a Core Value.
 *
 * `title` is the required anchor; `description` is optional rich text. The i18n
 * policy lives in ValidatesTranslations. Non-translatable: icon, display_order,
 * status.
 */
class CoreValueRequest extends FormRequest
{
    use ValidatesTranslations;

    /** Translatable base field => max length (null = unbounded text). */
    private const SPECS = [
        'title' => 191,
        'description' => null,
    ];

    protected function translatableSpecs(): array
    {
        return self::SPECS;
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge($this->translatableRules(), [
            'icon' => ['nullable', 'string', 'max:100'],
            'display_order' => [
                'required', 'integer', 'min:1',
                Rule::unique('core_values', 'display_order')->ignore($this->route('core_value')?->id),
            ],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }
}
