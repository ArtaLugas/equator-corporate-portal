@php
    $locales = config('locales.supported', []);
    $default = config('locales.default');

    $activeTab = $default;
    foreach (array_keys($locales) as $lc) {
        foreach (['position', 'bio'] as $f) {
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

    {{-- ===================================================== --}}
    {{-- CARD 1 : MEMBER INFORMATION --}}
    {{-- ===================================================== --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                Member Information
            </h2>
            <p class="mt-1 text-xs font-medium text-gray-500">
                Core details about the team member that will be published publicly.
            </p>
        </div>

        <div class="space-y-6">

            {{-- NAME (not translated — a personal identifier) --}}
            <x-admin.form.input name="name" label="Full Name"
                :value="old('name', $team->name ?? '')" placeholder="e.g. John Doe" required />

            {{-- EMAIL + LINKEDIN (not translated) --}}
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <x-admin.form.input name="email" label="Email Address" type="email"
                    :value="old('email', $team->email ?? '')" placeholder="e.g. john@example.com" />
                <x-admin.form.input name="linkedin_url" label="LinkedIn URL" type="url"
                    :value="old('linkedin_url', $team->linkedin_url ?? '')" placeholder="https://linkedin.com/in/username" />
            </div>

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

            {{-- LANGUAGE TABS (control position + bio) --}}
            <x-admin.lang-tabs />

            {{-- TRANSLATABLE FIELDS — one panel per locale --}}
            @foreach ($locales as $code => $meta)
                <div x-show="locale === '{{ $code }}'" x-cloak class="space-y-6">

                    <x-admin.form.input
                        name="position_{{ $code }}"
                        label="Position ({{ strtoupper($code) }})"
                        value="{{ old('position_' . $code, $team->{'position_' . $code} ?? '') }}"
                        placeholder="e.g. Chief Executive Officer"
                        :required="$code === $default" />

                    <x-admin.form.textarea
                        name="bio_{{ $code }}"
                        label="Biography ({{ strtoupper($code) }})"
                        rows="5"
                        :value="$team->{'bio_' . $code} ?? ''"
                        placeholder="Short professional biography about this member..." />

                </div>
            @endforeach

            {{-- PHOTO (not translated) --}}
            <div class="pt-2">
                <x-admin.image-preview name="photo" label="Member Photo"
                    helpText="Square (1:1) ratio recommended. Max 2MB."
                    :preview="isset($team) && $team->photo ? asset('storage/' . $team->photo) : null" />
            </div>

        </div>

    </div>

    {{-- ===================================================== --}}
    {{-- CARD 2 : VISIBILITY SETTINGS (not translated) --}}
    {{-- ===================================================== --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm sm:p-8">

        <div class="mb-6 border-b border-gray-50 pb-4">
            <h2 class="text-lg font-extrabold tracking-tight text-equator-text">
                Visibility Settings
            </h2>
            <p class="mt-1 text-xs font-medium text-gray-500">
                Configure publishing status and display order on the public page.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

            <x-admin.form.input name="display_order" label="Display Order" type="number" min="1"
                :value="old('display_order', $team->display_order ?? 1)" placeholder="1" />

            <x-admin.form.select name="status" label="Status" required>
                <option value="active" {{ old('status', $team->status ?? 'active') == 'active' ? 'selected' : '' }}>
                    Active
                </option>
                <option value="inactive" {{ old('status', $team->status ?? '') == 'inactive' ? 'selected' : '' }}>
                    Inactive
                </option>
            </x-admin.form.select>

        </div>

    </div>

</div>
