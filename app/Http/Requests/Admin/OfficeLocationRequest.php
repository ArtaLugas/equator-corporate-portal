<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesTranslations;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for creating/updating an Office Location.
 *
 * `name` is the required anchor; `address` is optional plain text. The i18n
 * policy lives in the shared ValidatesTranslations trait. Non-translatable:
 * phone, email, map_embed, display_order, status, is_primary.
 */
class OfficeLocationRequest extends FormRequest
{
    use ValidatesTranslations;

    /** Translatable base field => max length (null = unbounded text). */
    private const SPECS = [
        'name' => 191,
        'address' => null,
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
            'phone' => ['nullable', 'string', 'max:191'],
            'email' => ['nullable', 'email', 'max:191'],
            'map_embed' => ['nullable', 'string', 'max:5000'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'is_primary' => ['nullable', 'boolean'],
        ]);
    }
}
