<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Faq;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * FAQ i18n coverage, following the gold standard. FAQ has no slug/status; both
 * `question` and `answer` are user-facing and required in the default locale.
 * `answer` is plain text (not an HTML field), so there is no sanitization case.
 */
class FaqI18nTest extends TestCase
{
    use RefreshDatabase;

    private function faq(array $attributes = []): Faq
    {
        return Faq::create(array_merge([
            'question_en' => 'What is X?',
            'answer_en' => 'X is the answer.',
            'display_order' => 1,
        ], $attributes));
    }

    /*
    |--------------------------------------------------------------------------
    | Public rendering & fallback
    |--------------------------------------------------------------------------
    */

    public function test_public_faq_falls_back_to_english_when_translation_missing(): void
    {
        $this->faq(['question_en' => 'Refund policy', 'question_id' => null]);

        $this->get('/faq')->assertOk()->assertSee('Refund policy');
        $this->get('/id/faq')->assertOk()->assertSee('Refund policy'); // fallback
    }

    public function test_public_faq_shows_indonesian_when_available(): void
    {
        $this->faq(['question_en' => 'Refund policy', 'question_id' => 'Kebijakan pengembalian']);

        $this->get('/faq')->assertOk()->assertSee('Refund policy')->assertDontSee('Kebijakan pengembalian');
        $this->get('/id/faq')->assertOk()->assertSee('Kebijakan pengembalian');
    }

    public function test_translation_progress_reflects_completeness(): void
    {
        // EN has question + answer; ID has only question → 50%.
        $faq = $this->faq(['question_id' => 'Apa itu X?', 'answer_id' => null]);

        $this->assertSame(50, $faq->translationProgress('id'));

        $faq->update(['answer_id' => 'X adalah jawabannya.']);
        $this->assertTrue($faq->fresh()->isTranslated('id'));
    }

    /*
    |--------------------------------------------------------------------------
    | Admin form rendering
    |--------------------------------------------------------------------------
    */

    public function test_admin_create_form_renders_language_tabs(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.faqs.create'))
            ->assertOk()
            ->assertSee('Question (EN)')
            ->assertSee('Question (ID)');
    }

    public function test_admin_edit_form_prefills_localized_values(): void
    {
        $faq = $this->faq(['question_en' => 'Pricing', 'question_id' => 'Harga']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.faqs.edit', $faq))
            ->assertOk()
            ->assertSee('Pricing')
            ->assertSee('Harga');
    }

    /*
    |--------------------------------------------------------------------------
    | Per-locale validation
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_create_faq_with_both_locales(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.faqs.store'), [
                'question_en' => 'How do I contact support?',
                'question_id' => 'Bagaimana cara menghubungi dukungan?',
                'answer_en' => 'Use the contact form.',
                'answer_id' => 'Gunakan formulir kontak.',
                'display_order' => 1,
            ])
            ->assertRedirect(route('admin.faqs.index'));

        $this->assertDatabaseHas('faqs', [
            'question_en' => 'How do I contact support?',
            'question_id' => 'Bagaimana cara menghubungi dukungan?',
        ]);
    }

    public function test_english_question_and_answer_are_required(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.faqs.store'), [
                'question_en' => '',
                'answer_en' => '',
                'display_order' => 1,
            ])
            ->assertSessionHasErrors(['question_en', 'answer_en']);
    }

    public function test_indonesian_is_optional_when_untouched(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.faqs.store'), [
                'question_en' => 'English only question',
                'answer_en' => 'English only answer',
                'display_order' => 1,
            ])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect(route('admin.faqs.index'));
    }

    public function test_partial_indonesian_translation_is_rejected(): void
    {
        // EN complete; ID started (question_id) but answer_id left blank.
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.faqs.store'), [
                'question_en' => 'Question',
                'answer_en' => 'Answer',
                'question_id' => 'Pertanyaan',
                'answer_id' => '', // <-- missing → must fail
                'display_order' => 1,
            ])
            ->assertSessionHasErrors('answer_id')
            ->assertSessionDoesntHaveErrors('question_id')
            ->assertSessionHasErrors('translation_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Search across locales
    |--------------------------------------------------------------------------
    */

    public function test_search_matches_indonesian_term(): void
    {
        $this->faq(['question_en' => 'Pricing', 'question_id' => 'Harga']);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.faqs.index', ['search' => 'Harga']))
            ->assertOk()
            ->assertSee('Pricing');
    }
}
