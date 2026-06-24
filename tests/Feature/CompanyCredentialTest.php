<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\CompanyCredential;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Company Credentials — full coverage: i18n (title anchor, fallback, all-or-nothing),
 * CRUD, validation, search, category/status/featured filtering, child items,
 * expiry status badge, and the public index/detail pages with SEO.
 */
class CompanyCredentialTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        return Admin::factory()->create();
    }

    private function credential(array $attributes = []): CompanyCredential
    {
        return CompanyCredential::create(array_merge([
            'category' => 'iso',
            'title_en' => 'ISO 9001:2015',
            'issuer_en' => 'TÜV Rheinland',
            'description_en' => '<p>Quality management.</p>',
            'slug' => 'iso-9001-2015',
            'status' => 'active',
            'featured' => true,
            'display_order' => 1,
        ], $attributes));
    }

    /*
    |--------------------------------------------------------------------------
    | Translation resolution & progress
    |--------------------------------------------------------------------------
    */
    public function test_falls_back_to_english_when_translation_missing(): void
    {
        $c = $this->credential(['title_id' => null]);

        app()->setLocale('id');
        $this->assertSame('ISO 9001:2015', $c->title); // fallback to EN
    }

    public function test_resolves_indonesian_when_available(): void
    {
        $c = $this->credential(['title_en' => 'Business License', 'title_id' => 'Izin Usaha']);

        app()->setLocale('id');
        $this->assertSame('Izin Usaha', $c->title);
    }

    public function test_translation_progress_reflects_completeness(): void
    {
        // EN source has title + issuer + description = 3 fields.
        $c = $this->credential(['title_id' => 'ISO 9001:2015', 'issuer_id' => null, 'description_id' => null]);

        $this->assertSame(33, $c->translationProgress('id'));
    }

    public function test_html_description_sanitized_per_locale(): void
    {
        $c = $this->credential(['description_en' => '<p>Safe</p><script>alert(1)</script>']);

        $this->assertStringContainsString('Safe', $c->fresh()->description_en);
        $this->assertStringNotContainsString('<script>', $c->fresh()->description_en);
    }

    /*
    |--------------------------------------------------------------------------
    | Status badge (expiry-aware)
    |--------------------------------------------------------------------------
    */
    public function test_display_status_reflects_expiry(): void
    {
        $this->assertSame('active', $this->credential(['expiry_date' => null])->displayStatus());
        $this->assertSame('expired', $this->credential(['slug' => 'a', 'expiry_date' => now()->subDay()])->displayStatus());
        $this->assertSame('expiring_soon', $this->credential(['slug' => 'b', 'expiry_date' => now()->addDays(10)])->displayStatus());
        $this->assertSame('inactive', $this->credential(['slug' => 'c', 'status' => 'inactive'])->displayStatus());
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function test_scopes_filter_correctly(): void
    {
        $this->credential(['slug' => 'feat', 'featured' => true, 'status' => 'active']);
        $this->credential(['slug' => 'plain', 'featured' => false, 'status' => 'active']);
        $this->credential(['slug' => 'off', 'status' => 'inactive']);

        $this->assertSame(2, CompanyCredential::active()->count());
        $this->assertSame(1, CompanyCredential::active()->featured()->count());
    }

    public function test_search_matches_number_and_locale(): void
    {
        $this->credential(['slug' => 's1', 'title_en' => 'Quality Cert', 'title_id' => 'Sertifikat Mutu', 'credential_number' => 'QMS-123']);

        $this->assertSame(1, CompanyCredential::search('Sertifikat')->count());
        $this->assertSame(1, CompanyCredential::search('QMS-123')->count());
        $this->assertSame(0, CompanyCredential::search('nonexistent')->count());
    }

    /*
    |--------------------------------------------------------------------------
    | Admin CRUD + validation
    |--------------------------------------------------------------------------
    */
    public function test_admin_create_form_renders_language_tabs(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.company-credentials.create'))
            ->assertOk()
            ->assertSee('Title (EN)')
            ->assertSee('Title (ID)');
    }

    public function test_admin_can_create_credential_with_items(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.company-credentials.store'), [
                'category' => 'lpjp',
                'title_en' => 'LPJP Registration',
                'title_id' => 'Registrasi LPJP',
                'issuer_en' => 'Ministry',
                'issuer_id' => 'Kementerian',
                'description_en' => '<p>EN</p>',
                'description_id' => '<p>ID</p>',
                'status' => 'active',
                'display_order' => 1,
                'image' => UploadedFile::fake()->image('cert.png'),
                'items' => [
                    ['title_en' => 'Education Services', 'title_id' => 'Layanan Pendidikan', 'display_order' => 0],
                    ['title_en' => 'Health Services', 'title_id' => 'Layanan Kesehatan', 'display_order' => 1],
                ],
            ])
            ->assertRedirect(route('admin.company-credentials.index'));

        $this->assertDatabaseHas('company_credentials', [
            'title_en' => 'LPJP Registration',
            'slug' => 'lpjp-registration',
            'category' => 'lpjp',
        ]);
        $this->assertDatabaseCount('company_credential_items', 2);
        $this->assertDatabaseHas('company_credential_items', ['title_en' => 'Education Services']);
    }

    public function test_english_title_is_required(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.company-credentials.store'), [
                'category' => 'iso',
                'title_en' => '',
                'status' => 'active',
                'image' => UploadedFile::fake()->image('cert.png'),
            ])
            ->assertSessionHasErrors('title_en');
    }

    public function test_image_required_on_create(): void
    {
        $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.company-credentials.store'), [
                'category' => 'iso',
                'title_en' => 'No Image',
                'status' => 'active',
            ])
            ->assertSessionHasErrors('image');
    }

    public function test_invalid_category_rejected(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.company-credentials.store'), [
                'category' => 'not_a_real_category',
                'title_en' => 'X',
                'status' => 'active',
                'image' => UploadedFile::fake()->image('cert.png'),
            ])
            ->assertSessionHasErrors('category');
    }

    public function test_partial_indonesian_translation_is_rejected(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.company-credentials.store'), [
                'category' => 'iso',
                'title_en' => 'ISO',
                'issuer_en' => 'Body',
                'description_en' => '<p>EN</p>',
                'title_id' => 'ISO', // started ID…
                'issuer_id' => '',    // …but left these blank → must fail
                'description_id' => '',
                'status' => 'active',
                'image' => UploadedFile::fake()->image('cert.png'),
            ])
            ->assertSessionHasErrors(['issuer_id', 'description_id', 'translation_id']);
    }

    public function test_admin_can_update_and_delete_items(): void
    {
        Storage::fake('public');
        $c = $this->credential();
        $item = $c->items()->create(['title_en' => 'Old Item', 'display_order' => 0]);

        $this->actingAs($this->admin(), 'admin')
            ->put(route('admin.company-credentials.update', $c), [
                'category' => 'iso',
                'title_en' => 'ISO 9001:2015',
                'status' => 'active',
                'display_order' => 1,
                'deleted_items' => [$item->id],
                'items' => [
                    ['title_en' => 'New Item', 'display_order' => 0],
                ],
            ])
            ->assertRedirect(route('admin.company-credentials.index'));

        $this->assertDatabaseMissing('company_credential_items', ['id' => $item->id]);
        $this->assertDatabaseHas('company_credential_items', ['title_en' => 'New Item']);
    }

    public function test_category_filter_on_admin_index(): void
    {
        $this->credential(['slug' => 'iso-x', 'title_en' => 'ISO Cert', 'category' => 'iso']);
        $this->credential(['slug' => 'lpjp-x', 'title_en' => 'LPJP Cert', 'category' => 'lpjp']);

        $this->actingAs($this->admin(), 'admin')
            ->get(route('admin.company-credentials.index', ['category' => 'iso']))
            ->assertOk()
            ->assertSee('ISO Cert')
            ->assertDontSee('LPJP Cert');
    }

    public function test_soft_delete_and_restore(): void
    {
        $c = $this->credential();

        $this->actingAs($this->admin(), 'admin')
            ->delete(route('admin.company-credentials.destroy', $c))
            ->assertRedirect();
        $this->assertSoftDeleted($c);

        $this->actingAs($this->admin(), 'admin')
            ->patch(route('admin.company-credentials.restore', $c->id))
            ->assertRedirect();
        $this->assertNotSoftDeleted($c->fresh());
    }

    /*
    |--------------------------------------------------------------------------
    | Public pages + SEO
    |--------------------------------------------------------------------------
    */
    public function test_about_page_shows_active_credentials_with_items_and_hides_inactive(): void
    {
        Cache::flush(); // avoid stale cached About payload across tests

        $active = $this->credential(['slug' => 'pub-active', 'title_en' => 'Public Active', 'status' => 'active']);
        $active->items()->create(['title_en' => 'Scope Item', 'display_order' => 0]);
        $this->credential(['slug' => 'pub-off', 'title_en' => 'Hidden Inactive', 'status' => 'inactive']);

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('Public Active')
            ->assertSee('Scope Item')        // child items render inline on About
            ->assertDontSee('Hidden Inactive');
    }
}
