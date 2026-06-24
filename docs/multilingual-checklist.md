# Multilingual Implementation Checklist

Use this every time a module is made multilingual (FAQ, Core Values, Teams, and any future module). It is the standard derived from the **Service** gold standard — follow it; do not re-design the architecture. Full rationale: [`docs/multilingual-guide.md`](multilingual-guide.md).

**Conventions reminder**
- Only **user-facing** fields are translatable. Identifiers/internal fields (`key`, `slug`, `image`, `status`, `display_order`, FKs, dates, `email`, `phone`) stay single-column.
- `*_en` = source (default, required after Phase C). `*_id` = nullable, falls back to `*_en`.
- Slug is single-column, generated from the default-locale field.
- Static UI text → `lang/`; CMS content → DB columns.

---

## 1. Registry — `config/translatable.php`
- [ ] Add `'<table>' => ['fields' => [...], 'html' => [...]]`.
- [ ] `fields` = user-facing translatable columns only. `html` = rich-text fields (Purifier-sanitized per locale).
- [ ] Double-check no identifier/`key`/`slug` is listed.

## 2. Migrations (explicit, hand-written — see guide §7)
- [ ] **Phase A**: new migration adding `*_en` + `*_id` for each field, **mirroring the legacy column's exact type/length** (191 / 255 / 320 / longText).
- [ ] In Phase A, **relax** any legacy translatable column that is `NOT NULL` (no default) → nullable (so inserts work during transition).
- [ ] **Phase B**: backfill `UPDATE … SET field_en = field` for each field (leave `*_id` NULL).
- [ ] `php artisan migrate` (dev/test); confirm columns + types via introspection.
- [ ] Do **not** write Phase C here — it is the final gated step for the whole rollout.
- [ ] **Brand-new table (no legacy data)?** Skip Phase A/B/C entirely: create the table already in final shape (`*_en`/`*_id` columns, anchor `*_en` `NOT NULL`) in one `create` migration, and add **no** phase-c entry. Example: `company_credentials` / `company_credential_items` (2026-06-23).

## 3. Model
- [ ] `use App\Models\Concerns\HasTranslations;`
- [ ] `$fillable` = **non-translatable** columns only (trait appends `*_en`/`*_id`).
- [ ] Remove any old per-field Purifier mutator (trait sanitizes `html` fields for every locale).
- [ ] Keep relationships/scopes; nothing else changes.

## 4. FormRequest (`App\Http\Requests\Admin\<Module>Request`)
- [ ] `use App\Http\Requests\Concerns\ValidatesTranslations;`
- [ ] `private const SPECS = ['field' => maxLen|null, …];` + `protected function translatableSpecs(): array { return self::SPECS; }`
- [ ] `rules()` = `array_merge($this->translatableRules(), [ <non-translatable rules> ])`.
- [ ] If the anchor field is optional or different, override `requiredInDefaultLocale()` (e.g. `return [];`).
- [ ] If a `key`/derived field comes from the title, build it from the **default-locale** value in `prepareForValidation()`.

## 5. Controller
- [ ] `store()` / `update()` type-hint the FormRequest; use `$request->validated()`.
- [ ] Slug + image filename from the **default-locale** field (`$validated['name_'.config('locales.default')]`).
- [ ] On update, regenerate slug only when `config('cms.auto_regenerate_slug', true)` **and** the default-locale field changed.
- [ ] Localize **search**: loop `config('locales.supported')` over the name/title columns + slug + status + relations (category/etc.).
- [ ] Localize **sort**: `orderBy('<field>_'.config('locales.default'))`.
- [ ] Remove any old inline `validate()` / `validateData()` helper.

## 6. Admin form (`_form.blade.php`)
- [ ] Compute `$locales`, `$default`, `$activeTab` (first locale with a field error), `$autoSlug` (`!editing || config('cms.auto_regenerate_slug')`), `$translationSummaries`.
- [ ] Root `x-data` with `locale`, `autoSlug`, the default-locale name (for slug), `slug`, `generateSlug()` (guarded by `autoSlug`).
- [ ] All-or-nothing **summary alert** (loops `$translationSummaries`, reads `translation_{locale}`).
- [ ] `<x-admin.lang-tabs />` then a `@foreach($locales)` panel (`x-show="locale === '{code}'"`) with each translatable field; default-locale name bound `x-model`, others `value="{{ old(...) }}"`.
- [ ] Non-translatable fields (category, image, status, slug, dates…) **outside** the tabs.
- [ ] Use the **raw** column for prefill (`$model->{'field_'.$code}`), not the fallback accessor.

## 7. Admin index
- [ ] Add a `Translation` column header + `<x-admin.translation-status :model="$row" />` cell.
- [ ] Fix the empty-state `colspan` for the new column.

## 8. Public views
- [ ] Confirm rendering uses the trait accessor (`{{ $model->field }}` / `{!! $model->html !!}`) — usually no change needed.
- [ ] Localize public search with `->searchTranslatable($term, [...])` (or an inline locale loop).
- [ ] Any hardcoded UI text → `lang/` (`__('namespace.key')`), reusing `common.*` for shared strings.

## 9. Tests (`tests/Feature/<Module>I18nTest.php`)
- [ ] Public fallback to EN when ID missing; ID shown when present.
- [ ] `translationProgress()` / `isTranslated()` reflect completeness.
- [ ] Admin create form renders both language tabs; edit prefills both locales.
- [ ] EN anchor required; ID optional when untouched.
- [ ] **Partial ID rejected** → field error **and** `translation_{locale}` summary.
- [ ] Slug regenerates when default field changes (config on); frozen (config off).
- [ ] Search matches a non-default term + facets.
- [ ] HTML field sanitized per locale (for `html` fields).
- [ ] Update any **existing** tests that create rows with the old single-column field (`'name' => …` → `'name_en' => …`).

## 10. Validate (must pass before calling the module done)
- [ ] `php -l` + `vendor/bin/pint` on changed PHP — clean.
- [ ] en/id **key parity** for any new `lang/` namespace.
- [ ] `php artisan view:cache` compiles (then `view:clear`).
- [ ] Render the module's public pages in EN **and** `/id/...` → 200, content correct, ID falls back to EN where untranslated.
- [ ] `php artisan test` → **no new failures** vs the known baseline (the 8 pre-existing Turnstile failures).

---

### Report format (per module)
✅ Model · ✅ Trait · ✅ FormRequest · ✅ Validation · ✅ Controller · ✅ Search · ✅ Translation Status · ✅ Admin Form · ✅ Public View · ✅ Tests · ✅ Regression Check

Flag any new idea under **Future Improvements** in the guide instead of changing the frozen architecture.
