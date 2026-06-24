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

    {{-- GENERAL --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold text-equator-text">
                Core Value Information
            </h2>
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

            {{-- LANGUAGE TABS (control title + description) --}}
            <x-admin.lang-tabs />

            {{-- TRANSLATABLE FIELDS — one panel per locale --}}
            @foreach ($locales as $code => $meta)
                <div x-show="locale === '{{ $code }}'" x-cloak class="space-y-6">

                    <x-admin.form.input
                        name="title_{{ $code }}"
                        label="Title ({{ strtoupper($code) }})"
                        value="{{ old('title_' . $code, $coreValue->{'title_' . $code} ?? '') }}"
                        :required="$code === $default" />

                    <x-admin.form.wysiwyg
                        name="description_{{ $code }}"
                        label="Description ({{ strtoupper($code) }})"
                        :value="$coreValue->{'description_' . $code} ?? ''" />

                </div>
            @endforeach

            {{-- ICON (not translated) --}}
            @php
                $iconOptions = config('icons.lucide', []);
                $currentIcon = old('icon', $coreValue->icon ?? '');
            @endphp
            <div>
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end">

                        <div class="flex-1">
                            <x-admin.form.select name="icon" label="Icon">
                                <option value="">— No icon —</option>
                                @foreach ($iconOptions as $val => $label)
                                    <option value="{{ $val }}" @selected($currentIcon === $val)>{{ $label }}</option>
                                @endforeach
                                @if ($currentIcon && !array_key_exists($currentIcon, $iconOptions))
                                    <option value="{{ $currentIcon }}" selected>{{ $currentIcon }} (custom)</option>
                                @endif
                            </x-admin.form.select>
                        </div>

                        <div class="flex flex-col items-center gap-2">
                            <div id="icon-preview-wrapper"
                                class="flex h-16 w-16 items-center justify-center rounded-2xl border border-gray-200 bg-white shadow-sm">
                                <x-icon :name="$currentIcon ?: 'shield'" class="h-7 w-7 text-equator-text" />
                            </div>
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Preview</span>
                        </div>
                    </div>

                    <p class="mt-4 border-t border-gray-200 pt-4 text-xs text-gray-500">
                        Ikon diambil dari set kurasi (Lucide) agar bundle situs tetap ringan.
                    </p>
                </div>
            </div>

        </div>

    </div>

    {{-- SETTINGS (not translated) --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold text-equator-text">
                Visibility Settings
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            <x-admin.form.select name="status" label="Status">
                <option value="inactive" {{ old('status', $coreValue->status ?? '') == 'inactive' ? 'selected' : '' }}>
                    Inactive
                </option>
                <option value="active" {{ old('status', $coreValue->status ?? '') == 'active' ? 'selected' : '' }}>
                    Active
                </option>
            </x-admin.form.select>

            <x-admin.form.input type="number" name="display_order" label="Display Order"
                :value="old('display_order', $coreValue->display_order ?? 1)" />

        </div>

    </div>

</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('icon');
            const wrapper = document.getElementById('icon-preview-wrapper');

            if (!input || !wrapper) {
                return;
            }

            function renderIcon(iconName) {
                wrapper.innerHTML = `<i data-lucide="${iconName || 'shield'}" class="h-7 w-7 text-equator-text"></i>`;
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            }

            renderIcon(input.value.trim());
            input.addEventListener('change', () => renderIcon(input.value.trim()));
        });
    </script>
@endpush
