<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesTranslations;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for creating/updating an About History (company timeline) entry.
 *
 * `title` is the required anchor; `description` is optional rich text. The i18n
 * policy lives in ValidatesTranslations. Non-translatable: year, image, status,
 * display_order.
 */
class AboutHistoryRequest extends FormRequest
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
            'year' => [
                'required',
                'integer',
                'min:1900',
                'max:'.(date('Y') + 10),
            ],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'status' => [
                'required',
                Rule::in(['active', 'inactive']),
            ],
            'display_order' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('about_histories', 'display_order')->ignore($this->route('about_history')?->id),
            ],
        ]);
    }
}
