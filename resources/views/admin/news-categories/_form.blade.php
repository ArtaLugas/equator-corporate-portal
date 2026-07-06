@php
    $locales = config('locales.supported', []);
    $default = config('locales.default');

    // Focus the tab whose locale has a validation error.
    $activeTab = $default;
    foreach (array_keys($locales) as $lc) {
        if ($errors->has("name_{$lc}")) {
            $activeTab = $lc;
            break;
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
}" x-effect="generateSlug()" class="space-y-6">

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

    {{-- LANGUAGE TABS (control the translatable name below) --}}
    <x-admin.lang-tabs />

    {{-- TRANSLATABLE NAME — one panel per locale --}}
    @foreach ($locales as $code => $meta)
        <div x-show="locale === '{{ $code }}'" x-cloak>
            @if ($code === $default)
                <x-admin.form.input name="name_{{ $code }}" label="Category Name ({{ strtoupper($code) }})"
                    x-model="name" placeholder="e.g. Company Updates" :required="true" />
            @else
                <x-admin.form.input name="name_{{ $code }}" label="Category Name ({{ strtoupper($code) }})"
                    value="{{ old('name_' . $code, $category->{'name_' . $code} ?? '') }}"
                    placeholder="e.g. Company Updates" />
            @endif
        </div>
    @endforeach

    {{-- SLUG (auto, not translated — derived from the default-locale name) --}}
    <div class="space-y-1.5">
        <label class="block text-xs font-bold tracking-wide text-gray-700">URL Slug (auto)</label>
        <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-medium text-gray-500">
            <span class="text-gray-400">/</span><span x-text="slug || '...'"></span>
        </div>
    </div>

</div>
