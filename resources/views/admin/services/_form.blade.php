@php
    $locales = config('locales.supported', []);
    $default = config('locales.default');

    // Open the tab of the first locale that has a validation error, so a failed
    // ID translation surfaces on its own tab instead of staying hidden.
    $activeTab = $default;
    foreach (array_keys($locales) as $lc) {
        foreach (['name', 'short_description', 'description', 'meta_title', 'meta_description', 'meta_keywords'] as $f) {
            if ($errors->has("{$f}_{$lc}")) {
                $activeTab = $lc;
                break 2;
            }
        }
    }

    // Slug auto-regeneration: always on for new records; on edit it follows
    // config('cms.auto_regenerate_slug') so permalinks can be frozen post go-live.
    $editing = isset($service) && $service->exists;
    $autoSlug = ! $editing || config('cms.auto_regenerate_slug', true);

    // Locale-level all-or-nothing summary messages (shown above the tabs).
    $translationSummaries = collect(array_keys($locales))
        ->reject(fn ($l) => $l === $default)
        ->filter(fn ($l) => $errors->has("translation_{$l}"));
@endphp

<div x-data="{
    locale: @js($activeTab),
    autoSlug: @js($autoSlug),
    nameEn: @js(old('name_' . $default, $service->{'name_' . $default} ?? '')),
    slug: @js(old('slug', $service->slug ?? '')),
    generateSlug() {
        if (! this.autoSlug) return; // permalink frozen — keep the existing slug
        this.slug = this.nameEn
            .toString()
            .toLowerCase()
            .trim()
            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-')
            .replace(/^-+/, '')
            .replace(/-+$/, '');
    }
}" x-effect="generateSlug()">

    {{-- ===================================================== --}}
    {{-- CARD 1 : SERVICE INFORMATION --}}
    {{-- ===================================================== --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">

            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                Service Information
            </h2>

            <p class="mt-1 text-xs font-medium text-gray-500">
                Core details about the service that will be published publicly.
            </p>

        </div>

        {{-- CATEGORY (not translated) --}}
        <div class="mb-6 space-y-1.5">

            <label for="category_id" class="block text-xs font-bold tracking-wide text-gray-700">
                Service Category
            </label>

            <div class="relative">

                <select id="category_id" name="category_id" @class([
                    'appearance-none block w-full rounded-xl border px-4 py-2.5 text-sm font-bold text-equator-text shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-1 bg-white cursor-pointer',
                    'border-red-500 focus:border-red-500 focus:ring-red-500/30' => $errors->has('category_id'),
                    'border-gray-200 focus:border-equator-bright focus:ring-equator-bright/20' => !$errors->has('category_id'),
                ])>

                    <option value="">Select Category</option>

                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ old('category_id', $service->category_id ?? '') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach

                </select>

                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m6 9 6 6 6-6" />
                    </svg>
                </div>

            </div>

            @error('category_id')
                <p class="mt-1 flex items-start gap-1 text-xs font-semibold text-red-600">{{ $message }}</p>
            @enderror

        </div>

        {{-- ALL-OR-NOTHING TRANSLATION SUMMARY --}}
        @if ($translationSummaries->isNotEmpty())
            <div class="mb-5 flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="mt-0.5 shrink-0 text-amber-500">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                    <line x1="12" x2="12" y1="9" y2="13" />
                    <line x1="12" x2="12.01" y1="17" y2="17" />
                </svg>
                <div class="space-y-1">
                    @foreach ($translationSummaries as $l)
                        <p class="text-sm font-semibold text-amber-800">{{ $errors->first("translation_{$l}") }}</p>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- LANGUAGE TABS (control every translatable field below) --}}
        <x-admin.lang-tabs />

        {{-- TRANSLATABLE FIELDS — one panel per locale --}}
        @foreach ($locales as $code => $meta)
            <div x-show="locale === '{{ $code }}'" x-cloak class="space-y-6">

                {{-- NAME --}}
                <x-admin.form.input
                    name="name_{{ $code }}"
                    label="Service Name ({{ strtoupper($code) }})"
                    placeholder="e.g. Topographic Survey"
                    :required="$code === $default"
                    @if ($code === $default)
                        x-model="nameEn"
                    @else
                        value="{{ old('name_' . $code, $service->{'name_' . $code} ?? '') }}"
                    @endif
                />

                {{-- SHORT DESCRIPTION --}}
                <x-admin.form.textarea
                    name="short_description_{{ $code }}"
                    label="Short Description ({{ strtoupper($code) }})"
                    rows="3"
                    :value="$service->{'short_description_' . $code} ?? ''"
                    placeholder="Brief summary about this service..." />

                {{-- DESCRIPTION --}}
                <x-admin.form.wysiwyg
                    name="description_{{ $code }}"
                    label="Service Description ({{ strtoupper($code) }})"
                    :value="$service->{'description_' . $code} ?? ''" />

            </div>
        @endforeach

        {{-- SLUG (single, generated from the default-locale name) --}}
        <div class="mt-6">
            <x-admin.form.input name="slug" label="URL Slug" x-model="slug"
                placeholder="e.g. topographic-survey" readonly required />
        </div>

        {{-- IMAGE (not translated) --}}
        <div class="pt-6">
            <x-admin.image-preview name="image" label="Service Image"
                helpText="16:9 aspect ratio recommended. Max 2MB."
                :preview="isset($service) && $service->image ? asset('storage/' . $service->image) : null" />
        </div>

    </div>

    {{-- ===================================================== --}}
    {{-- CARD 2 : SEO SETTINGS --}}
    {{-- ===================================================== --}}
    <div class="mt-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">

            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                Search Engine Optimization (SEO)
            </h2>

            <p class="mt-1 text-xs font-medium text-gray-500">
                Per-language metadata. Use the language tabs above to switch.
            </p>

        </div>

        {{-- TRANSLATABLE SEO FIELDS — share the same locale tab as above --}}
        @foreach ($locales as $code => $meta)
            <div x-show="locale === '{{ $code }}'" x-cloak class="space-y-6">

                <x-admin.form.input
                    name="meta_title_{{ $code }}"
                    label="Meta Title ({{ strtoupper($code) }})"
                    placeholder="Maximum 60 characters"
                    value="{{ old('meta_title_' . $code, $service->{'meta_title_' . $code} ?? '') }}" />

                <x-admin.form.textarea
                    name="meta_description_{{ $code }}"
                    label="Meta Description ({{ strtoupper($code) }})"
                    rows="4"
                    :value="$service->{'meta_description_' . $code} ?? ''"
                    placeholder="Brief summary for search engines..." />

                <x-admin.form.input
                    name="meta_keywords_{{ $code }}"
                    label="Meta Keywords ({{ strtoupper($code) }})"
                    placeholder="e.g. survey, mapping, drone, lidar"
                    value="{{ old('meta_keywords_' . $code, $service->{'meta_keywords_' . $code} ?? '') }}" />

            </div>
        @endforeach

    </div>

    {{-- ===================================================== --}}
    {{-- CARD 3 : VISIBILITY SETTINGS (not translated) --}}
    {{-- ===================================================== --}}
    <div class="mt-6 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">

            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                Visibility Settings
            </h2>

            <p class="mt-1 text-xs font-medium text-gray-500">
                Configure service publishing and featured visibility.
            </p>

        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            <x-admin.form.select name="status" label="Status">
                <option value="draft" {{ old('status', $service->status ?? '') == 'draft' ? 'selected' : '' }}>
                    Draft
                </option>
                <option value="published" {{ old('status', $service->status ?? '') == 'published' ? 'selected' : '' }}>
                    Published
                </option>
            </x-admin.form.select>

            <x-admin.form.toggle name="is_featured" label="Featured Service"
                :checked="old('is_featured', $service->is_featured ?? false)">
                Featured services appear on homepage sections.
            </x-admin.form.toggle>

        </div>

    </div>

</div>
