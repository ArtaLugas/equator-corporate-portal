@php
    $locales = config('locales.supported', []);
    $default = config('locales.default');

    $activeTab = $default;
    foreach (array_keys($locales) as $lc) {
        foreach (['name', 'description', 'meta_title', 'meta_description', 'meta_keywords'] as $f) {
            if ($errors->has("{$f}_{$lc}")) {
                $activeTab = $lc;
                break 2;
            }
        }
    }

    $translationSummaries = collect(array_keys($locales))
        ->reject(fn ($l) => $l === $default)
        ->filter(fn ($l) => $errors->has("translation_{$l}"));
@endphp

<div x-data="{
    locale: @js($activeTab),
    name: @js(old('name_' . $default, $category->{'name_' . $default} ?? '')),
    slug: @js(old('slug', $category->slug ?? '')),

    generateSlug() {
        this.slug = this.name
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

    {{-- CARD 1: GENERAL INFORMATION --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Category Information</h2>
            <p class="mt-1 text-xs font-medium text-gray-500">Core details of the service that will be displayed
                publicly.</p>
        </div>

        <div class="space-y-6">

            {{-- ALL-OR-NOTHING TRANSLATION SUMMARY --}}
            @if ($translationSummaries->isNotEmpty())
                <div class="flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
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

            {{-- LANGUAGE TABS (control the translatable fields below) --}}
            <x-admin.lang-tabs />

            {{-- TRANSLATABLE FIELDS — one panel per locale --}}
            @foreach ($locales as $code => $meta)
                <div x-show="locale === '{{ $code }}'" x-cloak class="space-y-6">

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        {{-- NAME --}}
                        @if ($code === $default)
                            <x-admin.form.input name="name_{{ $code }}" label="Category Name ({{ strtoupper($code) }})"
                                x-model="name" placeholder="e.g. Topographic Mapping" :required="true" />
                        @else
                            <x-admin.form.input name="name_{{ $code }}" label="Category Name ({{ strtoupper($code) }})"
                                value="{{ old('name_' . $code, $category->{'name_' . $code} ?? '') }}"
                                placeholder="e.g. Topographic Mapping" />
                        @endif

                        {{-- SLUG (not translated; derived from the default-locale name) --}}
                        @if ($code === $default)
                            <x-admin.form.input name="slug" label="URL Slug" x-model="slug" readonly required />
                        @endif
                    </div>

                    {{-- DESCRIPTION (rich text) --}}
                    <x-admin.form.wysiwyg name="description_{{ $code }}"
                        label="Category Description ({{ strtoupper($code) }})"
                        :value="old('description_' . $code, $category->{'description_' . $code} ?? '')" />

                    {{-- META TITLE --}}
                    <x-admin.form.input name="meta_title_{{ $code }}" label="Meta Title ({{ strtoupper($code) }})"
                        :value="old('meta_title_' . $code, $category->{'meta_title_' . $code} ?? '')"
                        placeholder="Maximum 60 characters" />

                    {{-- META DESCRIPTION --}}
                    <div class="space-y-1.5">
                        <label for="meta_description_{{ $code }}"
                            class="block text-xs font-bold tracking-wide text-gray-700">
                            Meta Description ({{ strtoupper($code) }})
                        </label>
                        <textarea id="meta_description_{{ $code }}" name="meta_description_{{ $code }}" rows="4" @class([
                            'block w-full rounded-xl border px-4 py-3 text-sm text-equator-text shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:opacity-50 disabled:bg-gray-50',
                            'border-red-500 focus:border-red-500 focus:ring-red-500/30' => $errors->has(
                                "meta_description_{$code}"),
                            'border-gray-200 focus:border-equator-bright focus:ring-equator-bright/20' => !$errors->has(
                                "meta_description_{$code}"),
                        ])
                            placeholder="Write a brief summary for search engine results (Max 160 characters)...">{{ old('meta_description_' . $code, $category->{'meta_description_' . $code} ?? '') }}</textarea>

                        @error("meta_description_{$code}")
                            <p class="mt-1 flex items-start gap-1 text-xs font-semibold text-red-600">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="mt-0.5 shrink-0">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="12" x2="12" y1="8" y2="12" />
                                    <line x1="12" x2="12" y1="16" y2="16" />
                                </svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- META KEYWORDS --}}
                    <x-admin.form.input name="meta_keywords_{{ $code }}"
                        label="Meta Keywords ({{ strtoupper($code) }})"
                        :value="old('meta_keywords_' . $code, $category->{'meta_keywords_' . $code} ?? '')"
                        placeholder="e.g. mapping, survey, drone, topography" />

                </div>
            @endforeach

            {{-- IMAGE UPLOAD (not translated) --}}
            <div class="pt-2">
                <x-admin.image-preview name="image" label="Category Image"
                    helpText="16:9 aspect ratio recommended. Max 2MB." :preview="isset($category) && $category->image ? asset('storage/' . $category->image) : null" />
            </div>
        </div>
    </div>

    {{-- CARD 2: VISIBILITY & STATUS (not translated) --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">
        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">Visibility Settings</h2>
            <p class="mt-1 text-xs font-medium text-gray-500">Set the display order and availability of this category on
                the public site.</p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            <x-admin.form.select name="status" label="Status">

                <option value="inactive" {{ old('status', $category->status ?? '') == 'inactive' ? 'selected' : '' }}>
                    Inactive</option>
                <option value="active" {{ old('status', $category->status ?? '') == 'active' ? 'selected' : '' }}>Active
                </option>

            </x-admin.form.select>

            {{-- DISPLAY ORDER --}}
            <x-admin.form.input type="number" name="display_order" label="Display Order (Sort Order)" :value="old('display_order', $category->display_order ?? 0)"
                placeholder="Lower numbers appear first" min="1" required />
        </div>
    </div>

</div>
