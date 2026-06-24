@php
    $locales = config('locales.supported', []);
    $default = config('locales.default');

    $activeTab = $default;
    foreach (array_keys($locales) as $lc) {
        foreach (['title', 'subtitle', 'button_text'] as $f) {
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

    {{-- CARD : HERO INFORMATION --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">

            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                Hero Banner Information
            </h2>

            <p class="mt-1 text-xs font-medium text-gray-500">
                Main banner content displayed on homepage hero section.
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

            {{-- LANGUAGE TABS (control title + subtitle + button text) --}}
            <x-admin.lang-tabs />

            {{-- TRANSLATABLE FIELDS — one panel per locale --}}
            @foreach ($locales as $code => $meta)
                <div x-show="locale === '{{ $code }}'" x-cloak class="space-y-6">

                    {{-- TITLE --}}
                    <x-admin.form.input
                        name="title_{{ $code }}"
                        label="Banner Title ({{ strtoupper($code) }})"
                        value="{{ old('title_' . $code, $banner->{'title_' . $code} ?? '') }}"
                        placeholder="Example: Empowering Sustainable Development"
                        :required="$code === $default" />

                    {{-- SUBTITLE --}}
                    <div class="space-y-1.5">

                        <label for="subtitle_{{ $code }}" class="block text-xs font-bold tracking-wide text-gray-700">

                            Subtitle ({{ strtoupper($code) }})

                        </label>

                        <textarea id="subtitle_{{ $code }}" name="subtitle_{{ $code }}" rows="4" @class([
                            'block w-full rounded-xl border px-4 py-3 text-sm text-equator-text shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-1',

                            'border-red-500 focus:border-red-500 focus:ring-red-500/30' => $errors->has(
                                'subtitle_' . $code),

                            'border-gray-200 focus:border-equator-bright focus:ring-equator-bright/20' => !$errors->has(
                                'subtitle_' . $code),
                        ])
                            placeholder="Write banner subtitle here...">{{ old('subtitle_' . $code, $banner->{'subtitle_' . $code} ?? '') }}</textarea>

                        @error('subtitle_' . $code)
                            <p class="text-xs font-semibold text-red-600">

                                {{ $message }}

                            </p>
                        @enderror

                    </div>

                </div>
            @endforeach

            {{-- IMAGE --}}
            <div class="pt-2">

                <x-admin.image-preview name="image" label="Banner Image" helpText="Recommended ratio 16:9. Max 2MB."
                    :preview="isset($banner) && $banner->image ? asset('storage/' . $banner->image) : null" />

            </div>

        </div>

    </div>

    {{-- CARD : BUTTON SETTINGS --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">

            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                Button Settings
            </h2>

            <p class="mt-1 text-xs font-medium text-gray-500">
                Optional call-to-action button configuration.
            </p>

        </div>

        <div class="space-y-6">

            {{-- BUTTON TEXT (translatable — one input per locale) --}}
            @foreach ($locales as $code => $meta)
                <div x-show="locale === '{{ $code }}'" x-cloak>
                    <x-admin.form.input
                        name="button_text_{{ $code }}"
                        label="Button Text ({{ strtoupper($code) }})"
                        value="{{ old('button_text_' . $code, $banner->{'button_text_' . $code} ?? '') }}"
                        placeholder="Example: Learn More" />
                </div>
            @endforeach

            {{-- BUTTON LINK (not translated) --}}
            <x-admin.form.input name="button_link" label="Button Link" :value="old('button_link', $banner->button_link ?? '')"
                placeholder="https://example.com" />

        </div>

    </div>

    {{-- CARD : VISIBILITY --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">

            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                Visibility Settings
            </h2>

            <p class="mt-1 text-xs font-medium text-gray-500">
                Control homepage visibility and sorting order.
            </p>

        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            {{-- STATUS --}}
            <div class="space-y-1.5">

                <label for="status" class="block text-xs font-bold tracking-wide text-gray-700">

                    Status

                </label>

                <div class="relative">

                    <select id="status" name="status" @class([
                        'appearance-none block w-full rounded-xl border px-4 py-2.5 text-sm font-bold text-equator-text shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-1 bg-white cursor-pointer',
                    
                        'border-red-500 focus:border-red-500 focus:ring-red-500/30' => $errors->has(
                            'status'),
                    
                        'border-gray-200 focus:border-equator-bright focus:ring-equator-bright/20' => !$errors->has(
                            'status'),
                    ])>

                        <option value="active"
                            {{ old('status', $banner->status ?? '') === 'active' ? 'selected' : '' }}>

                            Active

                        </option>

                        <option value="inactive"
                            {{ old('status', $banner->status ?? '') === 'inactive' ? 'selected' : '' }}>

                            Inactive

                        </option>

                    </select>

                    {{-- CHEVRON --}}
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">

                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">

                            <path d="m6 9 6 6 6-6" />

                        </svg>

                    </div>

                </div>

            </div>

            {{-- DISPLAY ORDER --}}
            <x-admin.form.input type="number" name="display_order" label="Display Order" :value="old('display_order', $banner->display_order ?? 1)"
                min="1" placeholder="Lower numbers appear first" required />

        </div>

    </div>

</div>
