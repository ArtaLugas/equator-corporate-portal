<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesTranslations;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for creating/updating a FAQ item.
 *
 * Both `question` and `answer` are user-facing and REQUIRED in the default
 * locale (a FAQ needs both). The i18n policy (all-or-nothing for non-default
 * locales, summary message, attribute labels) lives in ValidatesTranslations.
 */
class FaqRequest extends FormRequest
{
    use ValidatesTranslations;

    /** Translatable base field => max length (matches the original rules). */
    private const SPECS = [
        'question' => 1000,
        'answer' => 10000,
    ];

    protected function translatableSpecs(): array
    {
        return self::SPECS;
    }

    /** Both fields are required in the default locale. */
    protected function requiredInDefaultLocale(): array
    {
        return ['question', 'answer'];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge($this->translatableRules(), [
            'display_order' => [
                'nullable', 'integer', 'min:0',
                Rule::unique('faqs', 'display_order')->ignore($this->route('faq')?->id),
            ],
        ]);
    }
}
