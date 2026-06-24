# i18n Architecture — Equator Corporate Portal

> ⚠️ **HISTORICAL DESIGN DOCUMENT — NOT THE OFFICIAL REFERENCE.**
> This note captures the **original design exploration** for multilingual support.
> Several decisions here **evolved during implementation** — e.g. migrations are
> **explicit hand-written** snapshots (not config-driven / no generator command),
> public routing uses a **plain + localized route pair**, and per-locale
> validation lives in the shared **`ValidatesTranslations`** trait.
>
> **The authoritative, up-to-date references are:**
> - [`docs/multilingual-guide.md`](multilingual-guide.md) — official implementation guide
> - [`docs/multilingual-checklist.md`](multilingual-checklist.md) — per-module standard
>
> Keep this file for historical context only; do not follow it for new work.

---

> **Stack:** Laravel 11 · Blade · Tailwind · Alpine · MySQL 5.7 · shared hosting
> **Strategy:** suffix columns (`title_en`, `title_id`) — no `spatie/laravel-translatable`, no JSON columns.
> **Default locale:** `en` (root URLs, canonical). **Additional:** `id` (prefixed `/id/...`).
> **Design goal:** column suffixes live in exactly **two places** (a config + a migration generator). Models, controllers, and views never hardcode `_en` / `_id`. Adding a 3rd language = edit config + run one generator + translate. No code rewrites.

---

## 0. Core principles

1. **Single source of truth.** Which fields are translatable and which locales exist are declared in config — never scattered across models/views.
2. **Convention over leakage.** A `HasTranslations` trait resolves `$model->title` to the active locale automatically. Views stay locale-agnostic (`{{ $service->title }}`), not `{{ $service->title_en }}`.
3. **Expand → migrate → contract.** Zero-downtime, reversible DB migration. Current English data is preserved as `*_en`.
4. **Fallback, never blank.** Missing `id` content falls back to `en` so the site is never half-empty during translation.
5. **Lightweight.** Plain `VARCHAR/TEXT` columns, indexed where needed. No runtime JSON parsing, no extra packages — ideal for MySQL 5.7 + shared hosting.

---

## 1. Single source of truth (config)

### `config/locales.php`
```php
<?php

return [
    // Default locale: served at root (/services), used as fallback, canonical for SEO.
    'default' => 'en',

    // Every supported locale. Add a key here to introduce a new language.
    'supported' => [
        'en' => [
            'name'   => 'English',
            'native' => 'English',
            'dir'    => 'ltr',
            'iso'    => 'en-US',   // for <html lang> + hreflang
        ],
        'id' => [
            'name'   => 'Indonesian',
            'native' => 'Bahasa Indonesia',
            'dir'    => 'ltr',
            'iso'    => 'id-ID',
        ],
    ],
];
```

### `config/translatable.php`
The registry the migration generator, the trait, validation, and tests all read from. **The only place you declare translatable fields.**
```php
<?php

return [
    // table => [ 'fields' => [...], 'html' => [...] ]
    // 'html' fields are sanitized with Purifier on write for every locale column.
    'services' => [
        'fields' => ['name', 'short_description', 'description', 'meta_title', 'meta_description', 'meta_keywords'],
        'html'   => ['description'],
    ],
    'projects' => [
        'fields' => ['title', 'summary', 'body', 'meta_title', 'meta_description', 'meta_keywords'],
        'html'   => ['body'],
    ],
    'news' => [
        'fields' => ['title', 'excerpt', 'body', 'meta_title', 'meta_description', 'meta_keywords'],
        'html'   => ['body'],
    ],
    'about_contents' => [
        'fields' => ['heading', 'body'],
        'html'   => ['body'],
    ],
    // ... faqs, core_values, teams (position/bio), service_categories, news_categories, hero_banners ...
];
```

> **Not translated** (stay single-column): `slug`, `image`, `status`, `is_featured`, `email`, `phone`, dates, foreign keys, `display_order`. **Slug is intentionally single** (generated from the default-locale field) to keep routing and route-model-binding simple — see §7.

---

## 2. Database structure & naming convention

**Rule:** every translatable field `X` becomes `X_<locale>` for each supported locale. Nothing else changes.

| Before | After (en + id) | Type | Null? |
|--------|-----------------|------|-------|
| `name` | `name_en` | `VARCHAR(255)` | `NOT NULL` (default locale required) |
|        | `name_id` | `VARCHAR(255)` | `NULL` (falls back to `_en`) |
| `description` | `description_en` | `TEXT` | `NOT NULL` |
|               | `description_id` | `TEXT` | `NULL` |

**Conventions**
- Default-locale column (`*_en`) is **`NOT NULL`** — the canonical content must always exist.
- Other-locale columns are **nullable** — enables progressive translation with `en` fallback.
- Keep the **same SQL type** the original column had (don't widen TEXT→LONGTEXT casually on 5.7).
- Indexing: only index a localized column if you query/sort on it (e.g. `news.title_en` if you order by title). Avoid indexing `TEXT` bodies; for search use `LIKE` (small dataset) or a dedicated search later. MySQL 5.7 has no functional indexes — suffix columns sidestep that entirely.

---

## 3. Migration strategy (expand → backfill → contract)

Three small, reversible migrations per release. Safe on a live DB and on shared hosting (no special privileges needed).

> **Decision — explicit, not config-driven (adopted).** The shipped migrations
> declare each column explicitly (`$table->string('name_en', 191)`,
> `$table->longText('content_en')`, …) rather than looping over config or
> introspecting `information_schema`. Rationale: a migration is a **frozen
> historical snapshot** of the schema and doubles as documentation; it must not
> change behaviour when `config/translatable.php` or `config/locales.php` later
> change. Runtime code (trait, search, switcher) stays config-driven for
> flexibility; the schema history stays explicit and immutable. The loop/
> introspection examples below are kept only to illustrate the three-phase
> *pattern* — the canonical reference is the actual files
> `database/migrations/2026_06_22_000001_*` (expand) and `..._000002_*`
> (backfill). Adding a 3rd language later means writing a new explicit migration
> (e.g. add `*_de` columns), not editing these.

### Phase A — Expand (add nullable localized columns)
```php
// database/migrations/2026_07_01_000001_i18n_expand_add_localized_columns.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (config('translatable') as $table => $meta) {
            if (! Schema::hasTable($table)) continue;

            Schema::table($table, function (Blueprint $t) use ($table, $meta) {
                foreach ($meta['fields'] as $field) {
                    foreach (array_keys(config('locales.supported')) as $locale) {
                        $col = "{$field}_{$locale}";
                        if (Schema::hasColumn($table, $col)) continue;

                        // Mirror the original column type where possible.
                        $isText = in_array($field, $meta['html'] ?? [], true)
                               || str_contains($field, 'description')
                               || in_array($field, ['body', 'summary', 'excerpt', 'bio'], true);

                        $column = $isText ? $t->text($col) : $t->string($col);
                        $column->nullable()->after($field); // all nullable during expand
                    }
                }
            });
        }
    }

    public function down(): void
    {
        foreach (config('translatable') as $table => $meta) {
            if (! Schema::hasTable($table)) continue;
            Schema::table($table, function (Blueprint $t) use ($table, $meta) {
                foreach ($meta['fields'] as $field) {
                    foreach (array_keys(config('locales.supported')) as $locale) {
                        $col = "{$field}_{$locale}";
                        if (Schema::hasColumn($table, $col)) $t->dropColumn($col);
                    }
                }
            });
        }
    }
};
```

### Phase B — Backfill (copy current English content into `*_en`)
```php
// 2026_07_01_000002_i18n_backfill_default_locale.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $default = config('locales.default'); // 'en'
        foreach (config('translatable') as $table => $meta) {
            if (! Schema::hasTable($table)) continue;
            foreach ($meta['fields'] as $field) {
                if (Schema::hasColumn($table, $field) && Schema::hasColumn($table, "{$field}_{$default}")) {
                    DB::table($table)->update(["{$field}_{$default}" => DB::raw("`{$field}`")]);
                }
            }
        }
    }

    public function down(): void
    {
        // Reverse copy so re-running expand/contract stays lossless.
        $default = config('locales.default');
        foreach (config('translatable') as $table => $meta) {
            if (! Schema::hasTable($table)) continue;
            foreach ($meta['fields'] as $field) {
                if (Schema::hasColumn($table, $field) && Schema::hasColumn($table, "{$field}_{$default}")) {
                    DB::table($table)->update([$field => DB::raw("`{$field}_{$default}`")]);
                }
            }
        }
    }
};
```

### Phase C — Contract (enforce NOT NULL on default, drop legacy columns)
Run **after** the application code (models/forms/views) is deployed and verified reading the new columns.
```php
// 2026_07_05_000001_i18n_contract_drop_legacy_and_enforce.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $default = config('locales.default');
        foreach (config('translatable') as $table => $meta) {
            if (! Schema::hasTable($table)) continue;

            // 1) Enforce NOT NULL on the default-locale columns (raw SQL keeps the exact type on 5.7).
            foreach ($meta['fields'] as $field) {
                $col  = "{$field}_{$default}";
                $type = in_array($field, $meta['html'] ?? [], true) ? 'TEXT' : 'VARCHAR(255)';
                // Guard against NULLs before enforcing.
                DB::table($table)->whereNull($col)->update([$col => '']);
                DB::statement("ALTER TABLE `{$table}` MODIFY `{$col}` {$type} NOT NULL");
            }

            // 2) Drop the now-unused legacy single-language columns.
            Schema::table($table, function ($t) use ($table, $meta) {
                foreach ($meta['fields'] as $field) {
                    if (Schema::hasColumn($table, $field)) $t->dropColumn($field);
                }
            });
        }
    }

    public function down(): void
    {
        // Re-create legacy columns and copy default-locale content back.
        $default = config('locales.default');
        foreach (config('translatable') as $table => $meta) {
            if (! Schema::hasTable($table)) continue;
            Schema::table($table, function ($t) use ($table, $meta) {
                foreach ($meta['fields'] as $field) {
                    if (! Schema::hasColumn($table, $field)) $t->text($field)->nullable();
                }
            });
            foreach ($meta['fields'] as $field) {
                DB::table($table)->update([$field => DB::raw("`{$field}_{$default}`")]);
            }
        }
    }
};
```

> **Why three phases?** Phase A+B can ship and be verified while the old `name` column still works (old code keeps running). Only after the new code is live do you Phase-C drop the legacy column. On a tiny dataset you *could* merge them, but the three-phase pattern is the enterprise-safe default and is what makes a future schema change painless.

### Adding a language later — write an explicit migration
Per the decision above, a new language is introduced with a new **explicit**
Phase-A migration (e.g. add the `*_de` columns), mirroring the lengths used here
(191 / 255 / 320 / longText). No generator command is used — keeping migrations
hand-written and frozen is the point. The runtime layer needs only a new entry
in `config/locales.php`; no model/controller/view/search code changes.

---

## 4. Model layer — `HasTranslations` trait

`app/Models/Concerns/HasTranslations.php` (matches your existing `Concerns` convention):

```php
<?php

namespace App\Models\Concerns;

use Mews\Purifier\Facades\Purifier;

trait HasTranslations
{
    /** Resolve translatable fields from the central registry (override per-model if needed). */
    public function translatableFields(): array
    {
        return $this->translatable
            ?? config('translatable.'.$this->getTable().'.fields', []);
    }

    public function translatableHtmlFields(): array
    {
        return $this->translatableHtml
            ?? config('translatable.'.$this->getTable().'.html', []);
    }

    /** Expand `['name']` → `['name_en','name_id', ...]` and merge into fillable automatically. */
    public function getFillable(): array
    {
        $locales = array_keys(config('locales.supported'));
        $expanded = [];
        foreach ($this->translatableFields() as $field) {
            foreach ($locales as $locale) {
                $expanded[] = "{$field}_{$locale}";
            }
        }
        return array_values(array_unique(array_merge($this->fillable, $expanded)));
    }

    /** Locale-aware resolution with fallback to the default locale. */
    public function translate(string $field, ?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();
        $value = $this->attributes["{$field}_{$locale}"] ?? null;

        if (($value === null || $value === '')) {
            $default = config('locales.default');
            if ($locale !== $default) {
                $value = $this->attributes["{$field}_{$default}"] ?? null;
            }
        }
        return $value;
    }

    /** Magic read: `$service->name` returns the active-locale value. Views stay locale-agnostic. */
    public function getAttribute($key)
    {
        if (in_array($key, $this->translatableFields(), true)
            && ! array_key_exists($key, $this->attributes)) {
            return $this->translate($key);
        }
        return parent::getAttribute($key);
    }

    /** Magic write: purify HTML translatable columns on set, for every locale. */
    public function setAttribute($key, $value)
    {
        $base = preg_replace('/_('.implode('|', array_keys(config('locales.supported'))).')$/', '', $key);
        if (in_array($base, $this->translatableHtmlFields(), true) && is_string($value)) {
            $value = Purifier::clean($value);
        }
        return parent::setAttribute($key, $value);
    }

    /** Search across all locale columns of the given fields. */
    public function scopeSearchTranslatable($query, ?string $term, array $fields)
    {
        if (blank($term)) return $query;
        $locales = array_keys(config('locales.supported'));
        return $query->where(function ($q) use ($term, $fields, $locales) {
            foreach ($fields as $field) {
                foreach ($locales as $locale) {
                    $q->orWhere("{$field}_{$locale}", 'like', "%{$term}%");
                }
            }
        });
    }
}
```

### Updated `Service` model (example)
```php
class Service extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    // Only NON-translatable columns here; the trait appends the *_en/*_id columns.
    protected $fillable = [
        'category_id', 'slug', 'image', 'status', 'is_featured',
    ];

    protected $casts = ['is_featured' => 'boolean'];

    // Purifier sanitation now handled centrally by the trait's setAttribute()
    // for every field listed under config('translatable.services.html').
    // → the old description() Attribute mutator is removed.

    public function category() { return $this->belongsTo(ServiceCategory::class, 'category_id'); }
    public function projects()  { return $this->belongsToMany(Project::class); }
}
```

> **Result:** Blade keeps `{{ $service->name }}` / `{!! $service->description !!}` unchanged — they now resolve per-locale with fallback. Zero `_en`/`_id` in views.

---

## 5. Controller layer

### Public controllers — almost no change
Locale is set by middleware (§7); the trait does resolution. The only change is **search** (query localized columns) and any `orderBy` on a translated field:

```php
// before: ->where('name', 'like', "%$q%")
$services = Service::query()
    ->searchTranslatable($request->q, ['name', 'short_description'])
    ->orderBy('name_'.config('locales.default')) // order by canonical column
    ->paginate(12);
```

Route-model binding by `slug` is unchanged (slug is single-column).

### Admin controllers — validation via FormRequest
```php
// app/Http/Requests/Admin/ServiceRequest.php
public function rules(): array
{
    $default = config('locales.default');
    $rules = [
        'category_id' => ['required', 'exists:service_categories,id'],
        'slug'        => ['nullable', 'string', 'max:255'],
        'status'      => ['required', 'in:draft,published'],
        'is_featured' => ['boolean'],
        'image'       => ['nullable', 'image', 'max:2048'],
    ];

    // Default locale required; others optional — driven by config, scales to N languages.
    foreach (config('translatable.services.fields') as $field) {
        foreach (array_keys(config('locales.supported')) as $locale) {
            $required = $locale === $default && in_array($field, ['name'], true) ? 'required' : 'nullable';
            $max      = in_array($field, config('translatable.services.html'), true) ? [] : ['max:255'];
            $rules["{$field}_{$locale}"] = array_merge([$required, 'string'], $max);
        }
    }
    return $rules;
}
```
`store()/update()` simply `$service->fill($request->validated())` — the trait’s expanded `fillable` accepts every `*_en/*_id` key. Slug generated from `name_en` via your existing `GeneratesUniqueSlug` concern.

---

## 6. Admin form — EN / ID tabs

Reusable, **config-driven** Alpine component that loops locales (so a 3rd tab appears automatically).

### `resources/views/components/admin/lang-tabs.blade.php`
```blade
@props(['default' => config('locales.default')])
@php $locales = config('locales.supported'); @endphp

<div x-data="{ locale: '{{ $default }}' }" class="rounded-lg border border-slate-200">
    {{-- Tab headers --}}
    <div class="flex gap-1 border-b border-slate-200 bg-slate-50 px-2 pt-2">
        @foreach ($locales as $code => $meta)
            <button type="button"
                    @click="locale = '{{ $code }}'"
                    :class="locale === '{{ $code }}'
                        ? 'bg-white border-slate-200 border-b-white text-equator'
                        : 'text-slate-500 hover:text-slate-700'"
                    class="-mb-px rounded-t-md border border-transparent px-4 py-2 text-sm font-semibold">
                {{ strtoupper($code) }}
                <span class="ml-1 text-xs font-normal text-slate-400">{{ $meta['native'] }}</span>
                @if ($code === config('locales.default'))
                    <span class="ml-1 rounded bg-equator/10 px-1 text-[10px] text-equator">default</span>
                @endif
            </button>
        @endforeach
    </div>

    {{-- Tab panels: same fields rendered once per locale --}}
    <div class="p-4">
        @foreach ($locales as $code => $meta)
            <div x-show="locale === '{{ $code }}'" x-cloak class="space-y-4">
                {{ $slot($code) ?? '' }}
            </div>
        @endforeach
    </div>
</div>
```

Because a scoped slot keeps Blade simple, the pragmatic pattern is a partial included per locale:

### `_form.blade.php` (Service) — fields looped per locale
```blade
<x-admin.lang-tabs>
    @foreach (config('locales.supported') as $code => $meta)
        <template x-if="locale === '{{ $code }}'"></template> {{-- marker, optional --}}
        <div x-show="locale === '{{ $code }}'" x-cloak class="space-y-4">
            <x-admin.input
                name="name_{{ $code }}"
                label="Name ({{ strtoupper($code) }})"
                :value="old('name_'.$code, $service->{'name_'.$code} ?? '')"
                :required="$code === config('locales.default')" />

            <x-admin.textarea
                name="short_description_{{ $code }}"
                label="Short description ({{ strtoupper($code) }})"
                :value="old('short_description_'.$code, $service->{'short_description_'.$code} ?? '')" />

            <x-admin.wysiwyg
                name="description_{{ $code }}"
                label="Description ({{ strtoupper($code) }})"
                :value="old('description_'.$code, $service->{'description_'.$code} ?? '')" />

            {{-- SEO meta per locale --}}
            <x-admin.input name="meta_title_{{ $code }}"       label="Meta title ({{ strtoupper($code) }})" ... />
            <x-admin.textarea name="meta_description_{{ $code }}" label="Meta description ({{ strtoupper($code) }})" ... />
        </div>
    @endforeach
</x-admin.lang-tabs>
```

> **Note:** access the raw column in admin forms (`$service->{'name_'.$code}`) — you want the *exact* stored value per locale here, **not** the fallback-resolved accessor. Only the public side uses fallback.

**Completeness badge (optional, recommended):** in the index list, show `ID ✓ / ID ⚠` by checking whether `name_id` is non-empty — gives editors a translation to-do view.

---

## 7. Routing & locale middleware

### Strategy: default locale at root, others prefixed
- `en` (default) → `/services`, `/news/foo` — **no prefix**, canonical.
- `id` → `/id/services`, `/id/news/foo`.
- An explicit `/en/...` request 301-redirects to the unprefixed canonical URL.

### `routes/web.php`
```php
$locales = implode('|', array_keys(config('locales.supported')));

Route::group([
    'prefix'     => '{locale?}',
    'where'      => ['locale' => $locales],
    'middleware' => 'setlocale',
], function () {
    Route::get('/',            [HomeController::class, 'index'])->name('home');
    Route::get('/services',    [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/{service:slug}', [ServiceController::class, 'show'])->name('services.show');
    // ... all public routes ...
});
```
Admin routes stay **outside** this group (CMS is English-only UI; not localized).

### `app/Http/Middleware/SetLocale.php`
```php
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(config('locales.supported'));
        $default   = config('locales.default');
        $locale    = $request->route('locale');

        // Canonical redirect: /en/... → /...  (avoid duplicate-content for SEO)
        if ($locale === $default) {
            $path = '/'.ltrim(preg_replace('#^/'.$default.'#', '', $request->getPathInfo()), '/');
            return redirect($path.($request->getQueryString() ? '?'.$request->getQueryString() : ''), 301);
        }

        if (! in_array($locale, $supported, true)) {
            $locale = $default; // unprefixed → default
        }

        app()->setLocale($locale);

        // Make route() preserve the active locale automatically in generated URLs.
        URL::defaults(['locale' => $locale === $default ? null : $locale]);
        $request->route()->forgetParameter('locale'); // keep {service} binding clean

        return $next($request);
    }
}
```
Register alias `setlocale` in `bootstrap/app.php` (Laravel 11 middleware registration).

> **Why URL prefix over session/cookie?** Each locale gets a **unique, shareable, indexable URL** — required for SEO and for the lead-gen goal (Google can rank the `id` pages separately). Session-only switching hides translations from crawlers.

`config/app.php`: keep `'locale' => 'en'`, `'fallback_locale' => 'en'`.

---

## 8. Language switcher, hreflang & sitemap

### Helper (`app/helpers.php`)
```php
if (! function_exists('locale_url')) {
    function locale_url(string $locale): string
    {
        $route   = Route::currentRouteName() ?: 'home';
        $params  = Route::current()?->parameters() ?? [];
        unset($params['locale']);
        if ($locale !== config('locales.default')) {
            $params = ['locale' => $locale] + $params;
        }
        return route($route, $params);
    }
}
```

### Switcher component `resources/views/components/public/lang-switcher.blade.php`
```blade
@php $current = app()->getLocale(); @endphp
<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="flex items-center gap-1 text-sm font-medium">
        {{ strtoupper($current) }}
        <x-icon name="chevron-down" class="h-4 w-4" />
    </button>
    <div x-show="open" @click.outside="open = false" x-cloak
         class="absolute right-0 mt-2 w-44 rounded-md border bg-white py-1 shadow-lg">
        @foreach (config('locales.supported') as $code => $meta)
            <a href="{{ locale_url($code) }}"
               hreflang="{{ $meta['iso'] }}"
               class="flex items-center justify-between px-3 py-2 text-sm
                      {{ $code === $current ? 'font-semibold text-equator' : 'text-slate-600 hover:bg-slate-50' }}">
                {{ $meta['native'] }}
                @if ($code === $current) <x-icon name="check" class="h-4 w-4" /> @endif
            </a>
        @endforeach
    </div>
</div>
```

### `<head>` — lang attribute, hreflang alternates, canonical
In `layouts/public.blade.php`:
```blade
<html lang="{{ config('locales.supported.'.app()->getLocale().'.iso') }}"
      dir="{{ config('locales.supported.'.app()->getLocale().'.dir') }}">
...
@foreach (config('locales.supported') as $code => $meta)
    <link rel="alternate" hreflang="{{ $meta['iso'] }}" href="{{ locale_url($code) }}">
@endforeach
<link rel="alternate" hreflang="x-default" href="{{ locale_url(config('locales.default')) }}">
<link rel="canonical" href="{{ url()->current() }}">
```

### Sitemap
Emit every public URL **once per locale** (loop `config('locales.supported')`), each entry carrying `<xhtml:link rel="alternate" hreflang>` to its counterparts. Drives multilingual indexing.

---

## 9. `lang/` folder — static UI strings

Static Blade text (buttons, nav, labels) is **not** in the DB — it goes in PHP lang files.

```
lang/
├── en/
│   ├── common.php      # nav, buttons, footer, generic ("Learn more", "Read more", "Submit")
│   ├── home.php        # homepage section copy
│   ├── contact.php     # form labels, success/error messages
│   ├── services.php
│   ├── projects.php
│   ├── news.php
│   └── validation.php  # (optional) localized validation messages
└── id/
    ├── common.php
    ├── home.php
    └── ... (mirror of en/)
```

Example `lang/en/common.php`:
```php
return [
    'learn_more'   => 'Learn more',
    'read_more'    => 'Read more',
    'all_services' => 'All Services',
    'get_in_touch' => 'Get in touch',
    'cta_title'    => "Let's Collaborate",
];
```
`lang/id/common.php`:
```php
return [
    'learn_more'   => 'Selengkapnya',
    'read_more'    => 'Baca selengkapnya',
    'all_services' => 'Semua Layanan',
    'get_in_touch' => 'Hubungi Kami',
    'cta_title'    => 'Mari Berkolaborasi',
];
```
Usage in Blade: `{{ __('common.learn_more') }}`, `@lang('home.hero_subtitle')`. With params: `__('news.published_on', ['date' => $news->published_at->translatedFormat('d F Y')])`.

> **Migration of existing hardcoded strings:** extract each literal (`"Learn more"`, `"All Services"`, …) from the 14 public Blade files into `lang/en/*.php` keys, replace with `{{ __('...') }}`, then translate the `id/` mirror. Mechanical, low-risk, do it module-by-module.

---

## 10. Adding a 3rd language later (proof of scalability)

Because everything is config-driven, adding e.g. Arabic (`ar`) is a **checklist, not a refactor**:

1. **Config:** add `'ar' => ['name' => 'Arabic', 'native' => 'العربية', 'dir' => 'rtl', 'iso' => 'ar']` to `config/locales.php`. *(Note `dir => rtl` already wired into `<html dir>`.)*
2. **DB:** write one explicit Phase-A migration adding the `*_ar` columns (mirror the 191/255/320/longText lengths), then `php artisan migrate`. *(Schema changes are always explicit & hand-written — see §3.)*
3. **Translate:** copy `lang/en/` → `lang/ar/` and translate; fill the AR tab in the admin (or leave blank → falls back to `en`).
4. **Done.** No model, controller, view, route, middleware, switcher, sitemap, or hreflang change — they all loop over `config('locales.supported')`.

So the *runtime* cost of a new language is one config entry plus translation; the only hand-written code is a single explicit migration. That separation — config-driven runtime, explicit frozen schema — is what makes suffix-columns enterprise-grade rather than a maintenance trap.

---

## 11. Performance & shared-hosting notes (MySQL 5.7)

- **No JSON, no joins for translation** → reads are plain column selects; fastest possible path, no N+1 from translation tables.
- **Row size:** many `TEXT` columns are stored off-page (pointer in row) on InnoDB — adding `*_id` TEXT columns won’t blow the 65 535-byte row limit. Watch only tables with *many* `VARCHAR(255)` translatable fields; convert borderline ones to `TEXT`.
- **Indexes:** add `INDEX(title_en)` only where you sort/filter. Don’t over-index on shared hosting.
- **Cache:** locale-segment your existing caches — include `app()->getLocale()` in cache keys (e.g. `services.index.{locale}.page.{n}`) so EN/ID don’t collide.
- **Config cache:** run `php artisan config:cache` after editing `config/locales.php` / `config/translatable.php` on deploy.
- **Opcache-friendly:** suffix columns + lang files are static PHP — no runtime package overhead.

---

## 12. Recommended rollout order

| Step | Scope | Risk |
|------|-------|------|
| 1 | Add `config/locales.php`, `config/translatable.php`, `HasTranslations` trait, `SetLocale` middleware, routing group, `locale_url()` helper, switcher, hreflang | Low (additive) |
| 2 | **Phase A + B migrations** for the 4 core public models (Service, Project, News, About) | Low (nullable + copy) |
| 3 | Update those 4 models + admin forms (lang-tabs) + FormRequests + public views/search | Medium |
| 4 | Verify EN renders identically; fill some ID content; test fallback + switcher + canonical redirect | — |
| 5 | **Phase C migration** (drop legacy cols, enforce NOT NULL) once verified | Medium (destructive — backup first) |
| 6 | Extract static strings → `lang/en` + `lang/id` (module by module) | Low |
| 7 | Remaining models (FAQ, Team, Partner, categories, hero) repeat steps 2–5 | Low |
| 8 | Localized sitemap + GA4 locale dimension | Low |

> **Backup before Phase C.** It drops the legacy columns; everything before it is reversible without data loss.
