<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesTranslations;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for creating/updating a Key Metric.
 *
 * Only the user-facing `label` is multilingual. The value, icon, display order,
 * status and featured flag stay single-language. The i18n policy lives in the
 * shared ValidatesTranslations trait.
 */
class KeyMetricRequest extends FormRequest
{
    use ValidatesTranslations;

    /** Translatable base field => max length. Only `label` is user-facing. */
    private const SPECS = [
        'label' => 191,
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
            'value' => ['required', 'string', 'max:50'],
            'icon' => ['nullable', 'string', 'max:100'],
            'display_order' => [
                'nullable', 'integer', 'min:0',
                Rule::unique('key_metrics', 'display_order')->ignore($this->route('key_metric')?->id),
            ],
            'status' => ['required', 'in:active,inactive'],
            'is_featured' => ['nullable', 'boolean'],
        ]);
    }
}
