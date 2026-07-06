<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesTranslations;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation for creating/updating a News Category.
 *
 * The i18n policy (EN required, ID all-or-nothing, summary message, localized
 * attribute labels) lives in the shared ValidatesTranslations trait. News
 * categories only translate the display name; the slug is derived from the
 * default-locale name in the controller.
 */
class NewsCategoryRequest extends FormRequest
{
    use ValidatesTranslations;

    /** Translatable base field => max length (null = unbounded text/longText). */
    private const SPECS = [
        'name' => 191,
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
        return $this->translatableRules();
    }
}
