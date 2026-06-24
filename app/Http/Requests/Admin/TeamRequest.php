<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesTranslations;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for creating/updating a Team member.
 *
 * Translatable: `position` (required anchor) and `bio` (optional plain text).
 * A member's `name` is a single-language identifier (required, non-translatable),
 * as are photo / email / linkedin / display_order / status. The i18n policy lives
 * in ValidatesTranslations.
 */
class TeamRequest extends FormRequest
{
    use ValidatesTranslations;

    /** Translatable base field => max length (null = unbounded text). */
    private const SPECS = [
        'position' => 191,
        'bio' => null,
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
            'name' => ['required', 'string', 'max:191'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'email' => ['nullable', 'email', 'max:191'],
            'linkedin_url' => ['nullable', 'url', 'max:500'],
            'display_order' => [
                'nullable', 'integer', 'min:1',
                // Unique per table; soft-deleted rows excluded so their order can be reused.
                Rule::unique('teams', 'display_order')->ignore($this->route('team')?->id)->whereNull('deleted_at'),
            ],
            'status' => ['required', 'in:active,inactive'],
        ]);
    }

    public function messages(): array
    {
        return [
            'display_order.unique' => 'This display order is already used by another member.',
        ];
    }
}
