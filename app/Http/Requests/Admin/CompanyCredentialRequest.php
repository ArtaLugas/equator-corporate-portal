<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesTranslations;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for creating/updating a Company Credential.
 *
 * The i18n policy (EN required, ID all-or-nothing, summary message, localized
 * attribute labels) lives in the shared ValidatesTranslations trait. Child items
 * are submitted inline with the parent form (repeater) and validated here too —
 * mirroring the Project gallery pattern. No separate item FormRequest.
 */
class CompanyCredentialRequest extends FormRequest
{
    use ValidatesTranslations;

    /** Translatable base field => max length (null = unbounded text/longText). */
    private const SPECS = [
        'title' => 191,
        'issuer' => 191,
        'description' => null,
    ];

    protected function translatableSpecs(): array
    {
        return self::SPECS;
    }

    public function authorize(): bool
    {
        // Route is behind admin.auth; resource authorization is enforced by
        // CompanyCredentialPolicy in the controller.
        return true;
    }

    public function rules(): array
    {
        $isCreate = $this->isMethod('post');

        return array_merge($this->translatableRules(), [
            'category' => ['required', 'string', Rule::in(array_keys(config('credentials.categories', [])))],
            'credential_number' => ['nullable', 'string', 'max:191'],
            'issue_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'image' => [$isCreate ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
            'attachment' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
            'remove_attachment' => ['nullable', 'boolean'],
            'verification_url' => ['nullable', 'url', 'max:500'],
            'featured' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive'],
            'display_order' => ['nullable', 'integer', 'min:0'],

            // Inline child items (repeater). title_en is each item's required anchor.
            'items' => ['nullable', 'array'],
            'items.*.id' => ['nullable', 'integer', 'exists:company_credential_items,id'],
            'items.*.title_en' => ['required', 'string', 'max:191'],
            'items.*.title_id' => ['nullable', 'string', 'max:191'],
            'items.*.description_en' => ['nullable', 'string', 'max:500'],
            'items.*.description_id' => ['nullable', 'string', 'max:500'],
            'items.*.display_order' => ['nullable', 'integer', 'min:0'],

            'deleted_items' => ['nullable', 'array'],
            'deleted_items.*' => ['integer'],
        ]);
    }
}
