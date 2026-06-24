<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesTranslations;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for creating/updating a Hero Banner.
 *
 * The user-facing title, subtitle and button text are multilingual; `title` is
 * the required default-locale anchor. The image, button link, status and display
 * order are single-language settings. The i18n policy lives in the shared
 * ValidatesTranslations trait.
 */
class HeroBannerRequest extends FormRequest
{
    use ValidatesTranslations;

    /** Translatable base field => max length. `title` is the required anchor. */
    private const SPECS = [
        'title' => 191,
        'subtitle' => 255,
        'button_text' => 100,
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
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'button_link' => [
                'nullable',
                'url',
                'max:500',
            ],
            'display_order' => [
                'nullable',
                'integer',
                'min:1',
                Rule::unique('hero_banners', 'display_order')->ignore($this->route('hero_banner')?->id),
            ],
            'status' => [
                'required',
                'in:active,inactive',
            ],
        ]);
    }

    public function messages(): array
    {
        return [
            'display_order.unique' => 'This display order is already used by another banner.',
        ];
    }
}
