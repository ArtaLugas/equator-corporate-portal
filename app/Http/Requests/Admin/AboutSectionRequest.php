<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesTranslations;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for creating/updating an About Section.
 *
 * Only the user-facing `name` is multilingual (it renders as a section heading).
 * The slug is an internal identifier generated in the controller. The i18n
 * policy lives in the shared ValidatesTranslations trait.
 */
class AboutSectionRequest extends FormRequest
{
    use ValidatesTranslations;

    /** Translatable base field => max length. Only `name` is user-facing. */
    private const SPECS = [
        'name' => 191,
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
        $section = $this->route('about_section');

        return array_merge($this->translatableRules(), [
            'display_order' => [
                'nullable', 'integer', 'min:1',
                Rule::unique('about_sections', 'display_order')->ignore($section?->id),
            ],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }

    public function messages(): array
    {
        return [
            'display_order.unique' => 'This display order is already used by another section.',
        ];
    }
}
