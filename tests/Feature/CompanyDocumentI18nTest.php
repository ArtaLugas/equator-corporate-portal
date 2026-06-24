<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\CompanyDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Company Document i18n coverage, following the gold standard. `title` is the
 * required anchor; `description` is optional rich text (sanitized per locale).
 * The slug is derived from the default-locale (English) title.
 */
class CompanyDocumentI18nTest extends TestCase
{
    use RefreshDatabase;

    private function document(array $attributes = []): CompanyDocument
    {
        return CompanyDocument::create(array_merge([
            'title_en' => 'Company Profile',
            'description_en' => '<p>Our profile.</p>',
            'slug' => 'company-profile',
            'file' => 'company-documents/profile.pdf',
            'document_type' => 'company_profile',
            'file_size' => 1024,
            'display_order' => 1,
            'status' => 'active',
        ], $attributes));
    }

    /*
    |--------------------------------------------------------------------------
    | Resolution & fallback
    |--------------------------------------------------------------------------
    */

    public function test_falls_back_to_english_when_translation_missing(): void
    {
        $document = $this->document(['title_en' => 'Capability Statement', 'title_id' => null]);

        app()->setLocale('en');
        $this->assertSame('Capability Statement', $document->title);

        app()->setLocale('id');
        $this->assertSame('Capability Statement', $document->title); // fallback
    }

    public function test_resolves_indonesian_when_available(): void
    {
        $document = $this->document([
            'title_en' => 'Capability Statement',
            'title_id' => 'Pernyataan Kapabilitas',
        ]);

        app()->setLocale('en');
        $this->assertSame('Capability Statement', $document->title);

        app()->setLocale('id');
        $this->assertSame('Pernyataan Kapabilitas', $document->title);
    }

    public function test_translation_progress_reflects_completeness(): void
    {
        $document = $this->document(['title_id' => 'Profil Perusahaan', 'description_id' => null]);

        $this->assertSame(50, $document->translationProgress('id'));

        $document->update(['description_id' => '<p>Profil kami.</p>']);
        $this->assertTrue($document->fresh()->isTranslated('id'));
    }

    /*
    |--------------------------------------------------------------------------
    | Admin form rendering
    |--------------------------------------------------------------------------
    */

    public function test_admin_create_form_renders_language_tabs(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.company-documents.create'))
            ->assertOk()
            ->assertSee('Document Title (EN)')
            ->assertSee('Document Title (ID)');
    }

    public function test_admin_edit_form_prefills_localized_values(): void
    {
        $document = $this->document([
            'title_en' => 'Capability Statement',
            'title_id' => 'Pernyataan Kapabilitas',
        ]);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.company-documents.edit', $document))
            ->assertOk()
            ->assertSee('Capability Statement')
            ->assertSee('Pernyataan Kapabilitas');
    }

    /*
    |--------------------------------------------------------------------------
    | Per-locale validation & slug
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_create_document_with_both_locales(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.company-documents.store'), [
                'title_en' => 'Corporate Brochure',
                'title_id' => 'Brosur Korporat',
                'description_en' => '<p>EN</p>',
                'description_id' => '<p>ID</p>',
                'document_type' => 'corporate_brochure',
                'file' => UploadedFile::fake()->create('brochure.pdf', 100, 'application/pdf'),
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.company-documents.index'));

        $this->assertDatabaseHas('company_documents', [
            'title_en' => 'Corporate Brochure',
            'title_id' => 'Brosur Korporat',
            'slug' => 'corporate-brochure', // derived from the English title
        ]);
    }

    public function test_english_title_is_required(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.company-documents.store'), [
                'title_en' => '',
                'file' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('title_en');
    }

    public function test_indonesian_is_optional_when_untouched(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.company-documents.store'), [
                'title_en' => 'English Only',
                'description_en' => '<p>EN</p>',
                'document_type' => 'other',
                'file' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertSessionDoesntHaveErrors()
            ->assertRedirect(route('admin.company-documents.index'));
    }

    public function test_partial_indonesian_translation_is_rejected(): void
    {
        $this->actingAs(Admin::factory()->create(), 'admin')
            ->post(route('admin.company-documents.store'), [
                'title_en' => 'Profile',
                'description_en' => '<p>EN</p>',
                'title_id' => 'Profil',
                'description_id' => '', // <-- missing → must fail
                'document_type' => 'company_profile',
                'file' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
                'display_order' => 1,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('description_id')
            ->assertSessionDoesntHaveErrors('title_id')
            ->assertSessionHasErrors('translation_id');
    }

    public function test_html_description_is_sanitized_per_locale(): void
    {
        $document = $this->document(['description_en' => '<p>Safe</p><script>alert(1)</script>']);

        $stored = $document->fresh()->description_en;
        $this->assertStringContainsString('Safe', $stored);
        $this->assertStringNotContainsString('<script>', $stored);
    }

    /*
    |--------------------------------------------------------------------------
    | Search across locales
    |--------------------------------------------------------------------------
    */

    public function test_search_matches_indonesian_term(): void
    {
        $this->document([
            'title_en' => 'Capability Statement',
            'title_id' => 'Pernyataan Kapabilitas',
        ]);

        $this->actingAs(Admin::factory()->create(), 'admin')
            ->get(route('admin.company-documents.index', ['search' => 'Pernyataan']))
            ->assertOk()
            ->assertSee('Capability Statement');
    }
}
