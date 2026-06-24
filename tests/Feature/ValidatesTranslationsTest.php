<?php

namespace Tests\Feature;

use App\Http\Requests\Concerns\ValidatesTranslations;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Direct coverage of the shared i18n validation mechanism, independent of any
 * single module — so a future change to ValidatesTranslations is caught here
 * and every module that uses it stays consistent.
 */
class ValidatesTranslationsTest extends TestCase
{
    /** A minimal FormRequest using the trait, with a representative field spec. */
    private function makeRequest(array $data): FormRequest
    {
        $request = new class extends FormRequest
        {
            use ValidatesTranslations;

            private const SPECS = [
                'title' => 50,
                'meta_title' => 60,
                'body' => null,
            ];

            protected function translatableSpecs(): array
            {
                return self::SPECS;
            }

            public function rules(): array
            {
                return $this->translatableRules();
            }
        };

        // GET input source = query bag; the trait reads via $this->input().
        $request->initialize($data);

        return $request;
    }

    public function test_default_anchor_required_others_nullable_with_max_lengths(): void
    {
        $rules = $this->makeRequest([])->rules();

        // Anchor (first field) required in the default locale; non-anchors nullable.
        $this->assertContains('required', $rules['title_en']);
        $this->assertContains('nullable', $rules['meta_title_en']);
        $this->assertContains('nullable', $rules['body_en']);

        // Non-default locale is always nullable, even for the anchor.
        $this->assertContains('nullable', $rules['title_id']);

        // Max lengths mirror the spec; the null-max field carries no max rule.
        $this->assertContains('max:50', $rules['title_en']);
        $this->assertContains('max:60', $rules['meta_title_id']);
        $this->assertEmpty(array_filter($rules['body_en'], fn ($r) => str_starts_with((string) $r, 'max:')));
    }

    public function test_untouched_non_default_locale_is_valid(): void
    {
        $data = ['title_en' => 'Hello', 'body_en' => 'World'];
        $request = $this->makeRequest($data);

        $validator = Validator::make($data, $request->rules());
        $request->withValidator($validator);

        $this->assertFalse($validator->fails());
    }

    public function test_started_locale_requires_all_source_fields_and_adds_summary(): void
    {
        // EN has title + body; ID started (title_id) but body_id left blank.
        $data = [
            'title_en' => 'Hello', 'body_en' => 'World',
            'title_id' => 'Halo', 'body_id' => '',
        ];
        $request = $this->makeRequest($data);

        $validator = Validator::make($data, $request->rules());
        $request->withValidator($validator);

        $this->assertTrue($validator->fails());

        $errors = $validator->errors();
        $this->assertTrue($errors->has('body_id'));          // missing field marked
        $this->assertFalse($errors->has('title_id'));        // filled → not flagged
        $this->assertTrue($errors->has('translation_id'));   // one locale-level summary

        // meta_title has no EN source, so its ID counterpart is NOT required.
        $this->assertFalse($errors->has('meta_title_id'));

        $this->assertStringContainsString(
            'Indonesian (ID) has been started',
            $errors->first('translation_id')
        );
    }

    public function test_attribute_labels_are_localized_and_humanized(): void
    {
        $attributes = $this->makeRequest([])->attributes();

        $this->assertSame('title (EN)', $attributes['title_en']);
        $this->assertSame('title (ID)', $attributes['title_id']);
        $this->assertSame('meta title (EN)', $attributes['meta_title_en']); // underscores → spaces
        $this->assertSame('body (ID)', $attributes['body_id']);
    }
}
