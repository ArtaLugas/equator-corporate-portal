@php
    $locales = config('locales.supported', []);
    $default = config('locales.default');

    $activeTab = $default;
    foreach (array_keys($locales) as $lc) {
        foreach (['title', 'content'] as $f) {
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

<div class="space-y-8" x-data="{ locale: @js($activeTab) }">

    {{-- GENERAL --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                About Content Information
            </h2>
            <p class="mt-1 text-xs font-medium text-gray-500">
                Configure about section content for public website.
            </p>
        </div>

        <div class="space-y-6">

            {{-- SECTION (not translated) --}}
            <x-admin.form.select name="section_id" label="About Section">
                <option value="">Select Section</option>
                @foreach ($sections as $section)
                    <option value="{{ $section->id }}" @selected(old('section_id', $content->section_id ?? '') == $section->id)>
                        {{ $section->name }}
                    </option>
                @endforeach
            </x-admin.form.select>

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

            {{-- LANGUAGE TABS (control title + content) --}}
            <x-admin.lang-tabs />

            {{-- TRANSLATABLE FIELDS — one panel per locale --}}
            @foreach ($locales as $code => $meta)
                <div x-show="locale === '{{ $code }}'" x-cloak class="space-y-6">

                    <x-admin.form.input
                        name="title_{{ $code }}"
                        label="Title ({{ strtoupper($code) }})"
                        placeholder="Enter content title"
                        value="{{ old('title_' . $code, $content->{'title_' . $code} ?? '') }}" />

                    <x-admin.form.wysiwyg
                        name="content_{{ $code }}"
                        label="Content ({{ strtoupper($code) }})"
                        :value="$content->{'content_' . $code} ?? ''" />

                </div>
            @endforeach

            {{-- KEY (single, internal identifier derived from the default-locale title) --}}
            <div>
                <x-admin.form.input name="key" label="Key (identifier)" :value="old('key', $content->key ?? '')"
                    readonly tabindex="-1" class="bg-gray-50 cursor-not-allowed text-gray-500"
                    placeholder="Auto from {{ strtoupper($default) }} title" />

                <p class="mt-1 text-xs font-medium text-gray-500">
                    Generated automatically from the <strong>{{ strtoupper($default) }}</strong> title (lowercase,
                    digits, underscore) and is <strong>read-only</strong>. It stays stable across translations.
                </p>
            </div>

            {{-- IMAGE (not translated) --}}
            <div class="pt-2">
                <x-admin.image-preview name="image" label="Content Image" helpText="Recommended 16:9 image."
                    :preview="isset($content) && $content->image ? asset('storage/' . $content->image) : null" />
            </div>

        </div>

    </div>

    {{-- SETTINGS --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                Visibility Settings
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            <x-admin.form.select name="status" label="Status">
                <option value="inactive" {{ old('status', $content->status ?? '') == 'inactive' ? 'selected' : '' }}>
                    Inactive
                </option>
                <option value="active" {{ old('status', $content->status ?? '') == 'active' ? 'selected' : '' }}>
                    Active
                </option>
            </x-admin.form.select>

            <x-admin.form.input type="number" name="display_order" label="Display Order"
                :value="old('display_order', $content->display_order ?? 1)"
                placeholder="Lower numbers appear first" />

        </div>

    </div>

</div>

{{-- Auto-generate Key from the DEFAULT-locale title (live). Key is read-only and
     stays stable across translations (it is never derived from a non-default title). --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const titleEl = document.getElementById('title_{{ $default }}');
        const keyEl = document.getElementById('key');

        if (!titleEl || !keyEl) {
            return;
        }

        const slugifyKey = (value) => value
            .toString()
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/_{2,}/g, '_')
            .replace(/^_+|_+$/g, '');

        titleEl.addEventListener('input', function () {
            keyEl.value = slugifyKey(titleEl.value);
        });
    });
</script>
