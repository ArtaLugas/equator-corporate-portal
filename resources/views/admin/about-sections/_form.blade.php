@php
    $locales = config('locales.supported', []);
    $default = config('locales.default');

    $activeTab = $default;
    foreach (array_keys($locales) as $lc) {
        if ($errors->has("name_{$lc}")) {
            $activeTab = $lc;
            break;
        }
    }

    $editing = isset($aboutSection) && $aboutSection->exists;
    $autoSlug = ! $editing || config('cms.auto_regenerate_slug', true);

    $translationSummaries = collect(array_keys($locales))
        ->reject(fn ($l) => $l === $default)
        ->filter(fn ($l) => $errors->has("translation_{$l}"));
@endphp

<div class="space-y-8" x-data="{
    locale: @js($activeTab),
    autoSlug: @js($autoSlug),
    nameEn: @js(old('name_' . $default, $aboutSection->{'name_' . $default} ?? '')),
    slug: @js(old('slug', $aboutSection->slug ?? '')),
    generateSlug() {
        if (! this.autoSlug) return;
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

    {{-- CARD 1 : SECTION INFORMATION --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                About Section Information
            </h2>
            <p class="mt-1 text-xs font-medium text-gray-500">
                Manage section grouping structure for About page content modules.
            </p>
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

            {{-- LANGUAGE TABS (control the translatable name) --}}
            <x-admin.lang-tabs />

            {{-- NAME — one panel per locale --}}
            @foreach ($locales as $code => $meta)
                <div x-show="locale === '{{ $code }}'" x-cloak>
                    <x-admin.form.input
                        name="name_{{ $code }}"
                        label="Section Name ({{ strtoupper($code) }})"
                        placeholder="e.g. Company Profile"
                        :required="$code === $default"
                        @if ($code === $default)
                            x-model="nameEn"
                        @else
                            value="{{ old('name_' . $code, $aboutSection->{'name_' . $code} ?? '') }}"
                        @endif
                    />
                </div>
            @endforeach

            {{-- SLUG (single, internal identifier from the default-locale name) --}}
            <x-admin.form.input name="slug" label="URL Slug" x-model="slug"
                placeholder="e.g. company-profile" readonly required />

        </div>

    </div>

    {{-- CARD 2 : VISIBILITY SETTINGS --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                Visibility Settings
            </h2>
            <p class="mt-1 text-xs font-medium text-gray-500">
                Configure display order and publication visibility for this section.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            <x-admin.form.select name="status" label="Status">
                <option value="inactive"
                    {{ old('status', $aboutSection->status ?? '') === 'inactive' ? 'selected' : '' }}>
                    Inactive
                </option>
                <option value="active" {{ old('status', $aboutSection->status ?? '') === 'active' ? 'selected' : '' }}>
                    Active
                </option>
            </x-admin.form.select>

            <x-admin.form.input type="number" name="display_order" label="Display Order"
                :value="old('display_order', $aboutSection->display_order ?? 1)"
                min="1" placeholder="Lower numbers appear first" required />

        </div>

    </div>

</div>
