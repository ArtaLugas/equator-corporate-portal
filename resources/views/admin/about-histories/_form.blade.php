@php
    $locales = config('locales.supported', []);
    $default = config('locales.default');

    $activeTab = $default;
    foreach (array_keys($locales) as $lc) {
        foreach (['title', 'description'] as $f) {
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

    {{-- GENERAL INFORMATION --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">

            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                History Information
            </h2>

            <p class="mt-1 text-xs font-medium text-gray-500">
                Manage company timeline and milestone information.
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

            {{-- YEAR (not translated) --}}
            <x-admin.form.input type="number" name="year" label="Year" :value="old('year', $aboutHistory->year ?? date('Y'))" min="1900"
                max="{{ date('Y') + 10 }}" required />

            {{-- LANGUAGE TABS (control title + description) --}}
            <x-admin.lang-tabs />

            {{-- TRANSLATABLE FIELDS — one panel per locale --}}
            @foreach ($locales as $code => $meta)
                <div x-show="locale === '{{ $code }}'" x-cloak class="space-y-6">

                    <x-admin.form.input
                        name="title_{{ $code }}"
                        label="Title ({{ strtoupper($code) }})"
                        value="{{ old('title_' . $code, $aboutHistory->{'title_' . $code} ?? '') }}"
                        placeholder="Example: Company Founded"
                        :required="$code === $default" />

                    <x-admin.form.wysiwyg
                        name="description_{{ $code }}"
                        label="Description ({{ strtoupper($code) }})"
                        :value="$aboutHistory->{'description_' . $code} ?? ''" />

                </div>
            @endforeach

            {{-- IMAGE --}}
            <div class="pt-2">

                <x-admin.image-preview name="image" label="Timeline Image"
                    helpText="Optional image for timeline milestone." :preview="isset($aboutHistory) && $aboutHistory->image
                        ? asset('storage/' . $aboutHistory->image)
                        : null" />

            </div>

        </div>

    </div>

    {{-- VISIBILITY SETTINGS --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">

            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                Visibility Settings
            </h2>

            <p class="mt-1 text-xs font-medium text-gray-500">
                Configure display order and publication status.
            </p>

        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            {{-- STATUS --}}
            <x-admin.form.select name="status" label="Status">

                <option value="active"
                    {{ old('status', $aboutHistory->status ?? 'active') === 'active' ? 'selected' : '' }}>
                    Active
                </option>

                <option value="inactive"
                    {{ old('status', $aboutHistory->status ?? '') === 'inactive' ? 'selected' : '' }}>
                    Inactive
                </option>

            </x-admin.form.select>

            {{-- DISPLAY ORDER --}}
            <x-admin.form.input type="number" name="display_order" label="Display Order" :value="old('display_order', $aboutHistory->display_order ?? 1)"
                min="1" placeholder="Lower numbers appear first" required />

        </div>

    </div>

</div>
