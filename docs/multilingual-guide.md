# Multilingual Implementation Guide (i18n)

> **Status:** Multilingual **v1** — complete & validated.
> **Stack:** Laravel 11 · Blade · Alpine · Tailwind · MySQL 5.7 · shared hosting.
> **Locales:** `en` (default, source, served at root) + `id` (additional, URL-prefixed `/id`).
> **Scope of v1:** core content modules **Service, Project, News, About** + the full public presentation layer (SEO + switcher + static-string extraction).

This is the **authoritative reference** for how multilingual works in this project. It supersedes the original design note `docs/i18n-architecture.md` (kept for history; some early ideas evolved — e.g. explicit migrations instead of config-driven, the plain+localized routing pair, and the shared `ValidatesTranslations` trait).

---

## 1. Architecture overview

Two layers, deliberately separated:

| Layer | What | Where it lives |
|---|---|---|
| **Dynamic content** (CMS) | Editor-authored data that differs per language: a service name, a news body, an about heading. | **Database** — suffix columns `name_en` / `name_id`, resolved at render by the `HasTranslations` model trait. |
| **Static UI** (interface chrome) | Fixed interface text: nav labels, buttons, footer, form labels, empty states. | **`lang/` files** — `lang/en/*.php` + `lang/id/*.php`, resolved by Laravel's `__()`. |

**Single sources of truth (config):**
- [`config/locales.php`](../config/locales.php) — which locales exist (`default`, `supported` with `name`/`native`/`dir`/`iso`). Everything (routing, middleware, trait, switcher, hreflang) loops over this. Add a language here.
- [`config/translatable.php`](../config/translatable.php) — registry of translatable DB fields per table (`fields` + `html`). Read by the model trait at runtime. **Migrations do NOT read it** (they are explicit, frozen snapshots).
- [`config/cms.php`](../config/cms.php) — `auto_regenerate_slug` (true pre-launch, set `false` after go-live to freeze permalinks).

**Key building blocks:**
- Model trait [`App\Models\Concerns\HasTranslations`](../app/Models/Concerns/HasTranslations.php) — locale-aware attribute resolution + fallback, Purifier sanitization of HTML fields, search scope, translation-progress.
- FormRequest trait [`App\Http\Requests\Concerns\ValidatesTranslations`](../app/Http/Requests/Concerns/ValidatesTranslations.php) — per-locale rules, EN-required anchor, ID all-or-nothing.
- Middleware [`App\Http\Middleware\SetLocale`](../app/Http/Middleware/SetLocale.php) — resolves the active locale from the URL, canonical redirect, URL generation defaults.
- Helper `locale_url()` in [`app/helpers.php`](../app/helpers.php) — current page in another locale (switcher + hreflang).
- Blade components: `<x-admin.lang-tabs>`, `<x-admin.translation-status>`, `<x-public.lang-switcher>`.

---

## 2. Request flow — middleware → render

```
/services            (English, default)            /id/services         (Indonesian)
       │                                                   │
       ▼                                                   ▼
routes/web.php                                      routes/web.php
  PLAIN group (unnamed) matches /services             LOCALIZED group {locale?} matches /id/services
       │                                                   │
       ▼                                                   ▼
SetLocale middleware                                 SetLocale middleware
  route('locale') = null → en                          route('locale') = 'id'
  URL::defaults(['locale' => ''])  (from boot)         app()->setLocale('id')
  app()->getLocale() = 'en'                            URL::defaults(['locale' => 'id'])
       │                                                   │
       ▼                                                   ▼
Public controller (e.g. ServiceController@show)       (same controller)
  Service::where('slug',$slug)->firstOrFail()          (same query — slug is single-column)
       │                                                   │
       ▼                                                   ▼
Blade render                                          Blade render
  {{ $service->name }}  → trait → name_en               {{ $service->name }} → trait → name_id
                                                                              (falls back to name_en if null)
  {{ __('nav.home') }}  → lang/en/nav.php                {{ __('nav.home') }} → lang/id/nav.php
  layout <head>: <html lang>, canonical, hreflang, og:locale (per active locale)
```

### Routing detail (the plain + localized pair)
Symfony drops the optionality of a leading `{locale?}` when a static segment follows, so a single `{locale?}/about` route **cannot** match both `/about` and `/id/about`. The public routes are therefore registered **twice from one closure** ([`routes/web.php`](../routes/web.php)):

1. **PLAIN, unprefixed, UNNAMED** → matches default-locale URLs (`/about`, `/services/{slug}`).
2. **LOCALIZED, `{locale?}`, NAMED** → matches prefixed URLs (`/id/about`) **and owns the route names**, so `route('about')` stays locale-aware via `URL::defaults`.

Only the localized group is named — naming both would break `route:cache` (Symfony requires unique route names). `URL::defaults(['locale' => ''])` is set globally in `AppServiceProvider::boot()` so **positional** `route()` calls (`route('news.show', $slug)`) keep their argument aligned to `{slug}` in every context (including the unprefixed sitemap, console, tests).

### Canonical & SEO (handled in [`layouts/public.blade.php`](../resources/views/layouts/public.blade.php))
- `/en/services` 301-redirects to `/services` (default locale is never prefixed).
- `<html lang="{iso}" dir="{dir}">` from the active locale.
- `<link rel="canonical">` = current localized URL.
- `<link rel="alternate" hreflang>` for every locale + `x-default`, via `locale_url()`.
- `og:locale` (`en_US` / `id_ID`) + `og:locale:alternate`.

---

## 3. Database conventions (`*_en`, `*_id`)

A translatable field `X` becomes `X_<locale>` per locale:

| Rule | Detail |
|---|---|
| **Default column** `X_en` | `NOT NULL` (enforced in Phase C). The source/canonical content. |
| **Other column** `X_id` | `NULL`able. Falls back to `X_en` when empty. |
| **Type/length** | Mirrors the original exactly: `varchar(191)` for indexed strings (utf8mb4 index-safe), `255`/`320` for others, `longText` for rich-text. |
| **Slug** | **Single column**, generated from the default-locale field (`name_en`/`title_en`). Keeps routing & route-model-binding simple. Not translated. |
| **Internal identifiers** | Stay single-column and are **never** translated: `key`, `slug`, `image`, `status`, `display_order`, foreign keys, dates, booleans, `email`, `phone`. |
| **HTML fields** | Listed under `html` in `config/translatable.php`; Purifier-sanitized on write **per locale** by the trait (replaces old per-model mutators). |

**Fallback semantics** (`HasTranslations::translate()`): for the active locale, return `X_<locale>`; if it is `null`/`''` and the locale is not the default, return `X_<default>`. The default locale returns its own value (no fallback). The public site is therefore never blank while a translation is in progress.

---

## 4. Creating a new multilingual module

High-level steps (the exact actionable list is in **`docs/multilingual-checklist.md`**):

1. **Registry** — add the table to [`config/translatable.php`](../config/translatable.php): `'fields' => [...]`, `'html' => [...]`. Only **user-facing** fields; never identifiers/keys.
2. **Migrations** — write an explicit **Phase A** (add `*_en`/`*_id` columns, mirror types; relax any `NOT NULL` legacy translatable column) and **Phase B** (backfill legacy → `*_en`). See §7.
3. **Model** — `use HasTranslations`; `$fillable` = non-translatable columns only; delete any old Purifier mutator (the trait handles it).
4. **FormRequest** — `use ValidatesTranslations`; declare `private const SPECS`; `rules()` = `array_merge($this->translatableRules(), [<non-translatable rules>])`; override `requiredInDefaultLocale()` if the anchor field differs or is optional.
5. **Controller** — type-hint the FormRequest; `$request->validated()`; slug from the default-locale field (config-gated, see §5); localized search/sort.
6. **Admin form** — `<x-admin.lang-tabs>` + per-locale fields + all-or-nothing summary alert + slug Alpine (`autoSlug`).
7. **Admin index** — add a `<x-admin.translation-status :model="$row" />` column.
8. **Public views** — render via the trait (`{{ $model->field }}`); search via `searchTranslatable()`.
9. **Tests** — a `<Module>I18nTest` mirroring the gold standard (Service).
10. **Validate** — en/id key parity, render EN+ID, full suite **zero regression**.

> **Gold standard:** [`Service`](../app/Models/Service.php) is the canonical reference. Project, News, and About follow it exactly, adapted to their fields. New modules must not re-design the architecture — only follow the pattern.

> **Brand-new table (no legacy data)?** Steps 2 collapses: there is no legacy single-language column to expand/backfill/contract, so **create the table already in its final shape** — add the `*_en`/`*_id` columns directly, with the anchor (`title_en`/`name_en`) `NOT NULL`. Skip Phase A/B/C entirely and add **no** phase-c entry. Everything else (registry, model, FormRequest, controller, views, `TranslationProgress`, tests) is identical. Worked example: **Company Credentials** (`company_credentials` + `company_credential_items`, 2026-06-23) — string-backed `category` (config-driven, no enum) and plain-text child items.

---

## 5. Implementation patterns

### Model
```php
use App\Models\Concerns\HasTranslations;

class Service extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    // NON-translatable columns only. The trait appends name_en/name_id/… to
    // $fillable (from config/translatable.php) and Purifier-sanitizes HTML fields.
    protected $fillable = ['category_id', 'slug', 'image', 'status', 'is_featured'];
}
```
- `{{ $service->name }}` resolves to the active locale (fallback to default). Access the **raw** column (`$service->name_id`) only in admin forms, where you want the exact stored value per locale.
- Search: `Service::searchTranslatable($term, ['name', 'short_description'])`.
- Progress: `$service->translationStat('id')` → `['translated'=>3,'source'=>5,'percent'=>60]`; `translationProgress('id')`; `isTranslated('id')`.

### FormRequest
```php
use App\Http\Requests\Concerns\ValidatesTranslations;

class ServiceRequest extends FormRequest
{
    use ValidatesTranslations;

    private const SPECS = [          // base field => max length (null = text/longText)
        'name' => 191, 'short_description' => 255, 'description' => null,
        'meta_title' => 191, 'meta_description' => 320, 'meta_keywords' => 255,
    ];

    protected function translatableSpecs(): array { return self::SPECS; }

    public function rules(): array
    {
        return array_merge($this->translatableRules(), [
            'category_id' => ['required', 'exists:service_categories,id'],
            'status'      => ['required', 'in:draft,published'],
            // …non-translatable rules…
        ]);
    }
}
```
The trait provides:
- **`translatableRules()`** — default-locale anchor required (first SPECS field, or override `requiredInDefaultLocale()`); every locale column nullable with its max length.
- **`withValidator()`** — ALL-OR-NOTHING: once any field of a non-default locale is filled, every field that has content in the default locale must also be translated; marks each missing field **and** adds one locale-level summary under `translation_{locale}`.
- **`attributes()`** — friendly `"<field> (<LOCALE>)"` labels (auto-generated).

For a module where the anchor is optional (e.g. About Content, whose `title` is optional), override:
```php
protected function requiredInDefaultLocale(): array { return []; }
```

### Controller (store / update)
```php
public function store(ServiceRequest $request)
{
    $validated = $request->validated();
    $validated['is_featured'] = $request->boolean('is_featured');

    $defaultName = $validated['name_'.config('locales.default')];
    $validated['slug'] = $this->generateUniqueSlug(Service::class, $defaultName);
    // image filename also uses $defaultName
    Service::create($validated);
}

public function update(ServiceRequest $request, Service $service)
{
    $validated = $request->validated();
    $default = config('locales.default');
    $defaultName = $validated['name_'.$default];

    // Regenerate slug ONLY when enabled AND the default-locale name changed.
    if (config('cms.auto_regenerate_slug', true) && $service->{'name_'.$default} !== $defaultName) {
        $validated['slug'] = $this->generateUniqueSlug(Service::class, $defaultName, $service->id);
    }
    $service->update($validated);
}
```
**Search** (admin index) covers every locale column plus slug/status/category:
```php
$query->where(function ($q) use ($search) {
    foreach (array_keys(config('locales.supported', [])) as $locale) {
        $q->orWhere("name_{$locale}", 'like', "%{$search}%");
    }
    $q->orWhere('slug', 'like', "%{$search}%")
      ->orWhere('status', 'like', "%{$search}%")
      ->orWhereHas('category', fn ($c) => $c->where('name', 'like', "%{$search}%"));
});
```
Sort localized fields by the default column: `->orderBy('name_'.config('locales.default'))`.

### Blade — admin form
```blade
@php
    $locales = config('locales.supported', []);
    $default = config('locales.default');
    // $activeTab opens the locale with a validation error; $autoSlug from config; $translationSummaries.
@endphp
<div x-data="{ locale: @js($activeTab), autoSlug: @js($autoSlug), nameEn: @js(old('name_'.$default, ...)), slug: ..., generateSlug() {...} }" x-effect="generateSlug()">
    {{-- all-or-nothing summary alert (loops $translationSummaries) --}}
    <x-admin.lang-tabs />
    @foreach ($locales as $code => $meta)
        <div x-show="locale === '{{ $code }}'" x-cloak>
            <x-admin.form.input name="name_{{ $code }}" label="Name ({{ strtoupper($code) }})"
                :required="$code === $default"
                @if ($code === $default) x-model="nameEn" @else value="{{ old('name_'.$code, $service->{'name_'.$code} ?? '') }}" @endif />
            {{-- short_description, description (wysiwyg)… --}}
        </div>
    @endforeach
    <x-admin.form.input name="slug" x-model="slug" readonly />
    {{-- non-translatable fields (category, image, status…) outside the tabs --}}
</div>
```
- Use the **raw** column in forms (`$service->{'name_'.$code}`), not the fallback accessor.
- Rich-text (`<x-admin.form.wysiwyg>`) uses `x-show` (DOM kept) so CKEditor initializes on every locale panel; ids are unique per locale (`description_en` / `description_id`).

### Blade — admin index
```blade
<x-admin.th>Translation</x-admin.th>
…
<x-admin.td><x-admin.translation-status :model="$service" /></x-admin.td>
```
Shows the default locale as source (`EN ✓`) and, per other locale, an explicit `3/5` badge (green = complete, amber = partial, grey = none).

### Blade — public
Nothing special: `{{ $service->name }}` / `{!! $service->description !!}` resolve automatically. Static UI uses `__('namespace.key')`.

### Testing
Mirror [`tests/Feature/ServiceI18nTest.php`](../tests/Feature/ServiceI18nTest.php). A module test should cover:
- Fallback to EN when the translation is missing; ID shown when present (public render).
- `translationProgress()` reflects completeness.
- Admin create form renders both language tabs; edit prefills both locales.
- EN anchor required; ID optional when untouched; **partial ID rejected** (field error + `translation_{locale}` summary).
- Slug regenerates when the default name changes (config on) / frozen (config off).
- Search matches a non-default term + facets.
- HTML field is sanitized per locale (for `html` fields).

The shared validation mechanism itself is covered by [`tests/Feature/ValidatesTranslationsTest.php`](../tests/Feature/ValidatesTranslationsTest.php).

---

## 6. Translation workflow (admin CMS)

1. The admin panel UI is **English-only**; only the content is bilingual.
2. Open a module's **Create/Edit** screen → a **language tab bar** (EN / ID) controls every translatable field across the form.
3. **EN is the source** — the anchor field (name/title) is required. Fill English first.
4. **ID is optional but all-or-nothing** — leaving ID empty is valid. The moment the editor fills *any* ID field, **every** field that has English content must also be translated before saving; incomplete ID fields are highlighted and a single summary explains why.
5. The `slug`/`key` are derived from the **English** value and stay stable across translations.
6. On the **index list**, a Translation column shows per-locale completeness (`ID 3/5`) so editors see what still needs translating without opening each record.
7. On the public site, any missing ID value **falls back to English** — the page is never blank.

---

## 7. Migration phases (expand → backfill → contract)

Three small, reversible, **explicit** migrations (hand-written snapshots, not config-driven). Phases A and B are applied; **Phase C is intentionally deferred** to the end of the whole rollout.

### Phase A — Expand (applied)
[`database/migrations/2026_06_22_000001_i18n_expand_add_localized_columns.php`](../database/migrations/2026_06_22_000001_i18n_expand_add_localized_columns.php)
- Adds nullable `*_en` / `*_id` columns for the core tables, **explicitly** mirroring each legacy column's exact type/length.
- **Relaxes** any legacy translatable column that is `NOT NULL` with no default to nullable (the only one is `about_sections.name`), so new code — which writes only `*_en`/`*_id` — can insert during the transition.

### Phase B — Backfill (applied)
[`database/migrations/2026_06_22_000002_i18n_backfill_default_locale.php`](../database/migrations/2026_06_22_000002_i18n_backfill_default_locale.php)
- Copies each legacy column into its `*_en` column (existing content is English). `*_id` stays `NULL` (so translation status is explicit + fallback keeps the site complete).

### Phase C — Contract (PREPARED, GATED — not run)
The final migration is **written and statically reviewed** but **not run**. It is
treated as a **production-readiness / go-live** step, not feature work — the
legacy columns are kept as a rollback safety net throughout staging and UAT.

- **File:** `database/migrations/phase-c/2026_06_22_000009_i18n_contract_drop_legacy_and_enforce.php`
- **Why a subfolder:** Laravel's migrator scans only `database/migrations`
  (non-recursive), so this migration is **never** picked up by `php artisan
  migrate`, `migrate:fresh`, or the test suite. It runs only when invoked
  explicitly with `--path` (see the runbook).
- **What it does:**
  1. Enforces `NOT NULL` on the `*_en` column of each module's **required anchor
     field(s) only** — `services.name_en`, `projects.name_en`, `news.title_en`,
     `about_sections.name_en`, `faqs.question_en` + `answer_en`,
     `core_values.title_en`, `teams.position_en`. **Optional** translatable
     columns (descriptions, bodies, meta, bio, `about_contents.title`/`content`)
     stay nullable.
  2. Drops every legacy single-language column.
- **Reversible:** `down()` re-creates the legacy columns (nullable), copies `*_en`
  back, and relaxes the enforced columns — a lossless rollback.
- **Execution runbook + rollback steps:** [`docs/phase-c-execution.md`](phase-c-execution.md).

**Phase C gate — run only after ALL of:**
1. ✅ Every translatable module has adopted the trait — **all 8 registry tables
   done** (Service, Project, News, About×2, FAQ, Core Values, Teams).
2. ✅ Every admin form, FormRequest, public view, and search is localized.
3. ✅ Static strings extracted to `lang/` (Multilingual v1).
4. ☐ Verified on staging: EN renders identically to pre-i18n, ID content filled, fallback works.
5. ☐ UAT signed off.
6. ☐ **Database backed up** (Phase C is destructive).
7. ☐ Final approval recorded.

Until Phase C runs, the legacy columns remain a **rollback safety net**: the trait
reads the new columns, the legacy columns sit untouched, and reverting the code
makes the app read them again with no data loss.

### Running migrations
```bash
php artisan migrate            # apply Phase A + B (additive, safe; auto-discovered)
php artisan migrate:rollback   # reversible (lossless before Phase C)

# Phase C — gated, run ONLY at go-live per docs/phase-c-execution.md:
php artisan migrate --path=database/migrations/phase-c --force            # contract
php artisan migrate:rollback --path=database/migrations/phase-c --force   # rollback
```

---

## 8. Adding a third language later

Because the runtime is config-driven, a new language (e.g. `de`) is a checklist, not a refactor:
1. Add the entry to `config/locales.php` (`name`, `native`, `dir`, `iso`).
2. Write one **explicit** Phase-A migration adding the `*_de` columns (mirror 191/255/320/longText); `php artisan migrate`.
3. Copy `lang/en/` → `lang/de/` and translate; fill the DE tab in the admin (or leave blank → falls back to default).
4. Done — no model/controller/view/route/middleware/switcher/sitemap/hreflang code changes (they all loop over `config('locales.supported')`).

After editing any config, run `php artisan config:cache` on deploy.

---

## 9. Future Improvements (deferred under architecture freeze)

Recorded during v1; evaluate after the remaining modules are migrated. **None block FAQ/Core Values/Teams.**

| # | Item | Notes |
|---|---|---|
| 1 | **`trans_choice` for EN plurals** | Some count strings use a flat form (e.g. "1 services matched"). ID has no inflection; EN exactness needs `trans_choice`. |
| 2 | **JSON-LD breadcrumb localization** | Structured-data `'Home'`/section names left English (machine-readable, not visible UI). |
| 3 | **Settings i18n** | `app_setting('company_name'/'tagline'/'meta_*')` are single-language; the home/services `<title>` fall back to them. A settings-level i18n is a separate concern. |
| 4 | **Dead import** | `use Illuminate\Support\Str;` is now unused in `resources/views/public/news/index.blade.php`. |
| 5 | **`strip_tags(null)` deprecation** | `resources/views/public/services/show.blade.php:5` — pre-existing; add a `(string)` cast. |
| 6 | **Dead code** | `AboutSection::contentByTitle()` is unused (title is now locale-resolved). |
| 7 | **Extract admin-form scaffolding** | The lang-tabs + summary alert + slug Alpine block repeats across module forms; could become a shared partial/component (analogous to the `ValidatesTranslations` extraction). |
| 8 | **`i18n:scaffold` generator** | Optional artisan command to stub a Phase-A migration for a new language (we chose hand-written explicit migrations; a generator is a convenience only). |

---

## Reference index

| Concern | File |
|---|---|
| Locale config | `config/locales.php` |
| Translatable registry | `config/translatable.php` |
| Slug/permalink config | `config/cms.php` |
| Model trait | `app/Models/Concerns/HasTranslations.php` |
| Validation trait | `app/Http/Requests/Concerns/ValidatesTranslations.php` |
| Locale middleware | `app/Http/Middleware/SetLocale.php` |
| Global URL default | `app/Providers/AppServiceProvider.php` |
| Routing (plain + localized) | `routes/web.php` |
| `locale_url()` helper | `app/helpers.php` |
| Language tabs (admin) | `resources/views/components/admin/lang-tabs.blade.php` |
| Translation status (admin) | `resources/views/components/admin/translation-status.blade.php` |
| Language switcher (public) | `resources/views/components/public/lang-switcher.blade.php` |
| Public `<head>` SEO | `resources/views/layouts/public.blade.php` |
| Static UI strings | `lang/en/*.php`, `lang/id/*.php` |
| Reference module (gold standard) | `app/Models/Service.php` + `ServiceRequest` + `ServiceController` + `ServiceI18nTest` |
| New-module checklist | `docs/multilingual-checklist.md` |
