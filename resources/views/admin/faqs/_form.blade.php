@php
    $locales = config('locales.supported', []);
    $default = config('locales.default');

    // Open the tab of the first locale that has a validation error.
    $activeTab = $default;
    foreach (array_keys($locales) as $lc) {
        foreach (['question', 'answer'] as $f) {
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

<div class="space-y-6" x-data="{ locale: @js($activeTab) }">

    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">FAQ Item</h2>
            <p class="mt-1 text-xs font-medium text-gray-500">A question and its answer for the public FAQ section.</p>
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

            {{-- LANGUAGE TABS (control question + answer) --}}
            <x-admin.lang-tabs />

            {{-- TRANSLATABLE FIELDS — one panel per locale --}}
            @foreach ($locales as $code => $meta)
                <div x-show="locale === '{{ $code }}'" x-cloak class="space-y-6">

                    <x-admin.form.textarea
                        name="question_{{ $code }}"
                        label="Question ({{ strtoupper($code) }})"
                        rows="2"
                        :value="$faq->{'question_' . $code} ?? ''"
                        placeholder="e.g. What services do you provide?"
                        :required="$code === $default" />

                    <x-admin.form.textarea
                        name="answer_{{ $code }}"
                        label="Answer ({{ strtoupper($code) }})"
                        rows="6"
                        :value="$faq->{'answer_' . $code} ?? ''"
                        placeholder="Write a clear, helpful answer..."
                        :required="$code === $default" />

                </div>
            @endforeach

            {{-- DISPLAY ORDER (not translated) --}}
            <div class="max-w-xs">
                <x-admin.form.input name="display_order" label="Display Order" type="number" min="0"
                    :value="old('display_order', $faq->display_order ?? 0)" placeholder="0" />
            </div>

        </div>
    </div>

</div>
