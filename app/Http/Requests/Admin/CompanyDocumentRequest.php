<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesTranslations;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation for creating/updating a Company Document.
 *
 * `title` is the required anchor; `description` is optional rich text. The i18n
 * policy (EN required, ID all-or-nothing, summary message, localized attribute
 * labels) lives in the shared ValidatesTranslations trait. Non-translatable:
 * document_type, file, thumbnail, display_order, status.
 */
class CompanyDocumentRequest extends FormRequest
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
        // Route is already behind the admin.auth middleware.
        return true;
    }

    public function rules(): array
    {
        // `file` is required on create, optional on update (an existing document
        // already has a stored file).
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');

        return array_merge($this->translatableRules(), [
            'document_type' => [
                'required',
                Rule::in([
                    'company_profile',
                    'capability_statement',
                    'corporate_brochure',
                    'presentation',
                    'other',
                ]),
            ],
            'file' => [
                $isUpdate ? 'nullable' : 'required',
                'mimes:pdf',
                'max:20480',
            ],
            'thumbnail' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
            'display_order' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('company_documents', 'display_order')
                    ->ignore($this->route('company_document')?->id)
                    ->whereNull('deleted_at'),
            ],
            'status' => [
                'required',
                Rule::in(['active', 'inactive']),
            ],
        ]);
    }
}
